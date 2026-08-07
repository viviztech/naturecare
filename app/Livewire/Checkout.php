<?php

namespace App\Livewire;

use App\Enums\PaymentMethod;
use App\Exceptions\CartValidationException;
use App\Livewire\Concerns\ValidatesPincode;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use App\Support\Money;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class Checkout extends Component
{
    use ValidatesPincode;

    public int $step = 1;

    public string $name = '';

    public string $mobile = '';

    public string $email = '';

    public string $line1 = '';

    public string $line2 = '';

    public string $city = '';

    public string $pincode = '';

    public string $state = 'Tamil Nadu';

    public string $paymentMethod = 'cod';

    public ?int $shippingChargePaise = null;

    public bool $codAvailable = false;

    /** @var array<int, string> */
    public array $cartIssues = [];

    public ?string $orderNumber = null;

    public ?string $razorpayOrderId = null;

    public ?string $razorpayKey = null;

    public ?int $razorpayAmountPaise = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'digits:6'],
            'paymentMethod' => ['required', 'in:cod,razorpay'],
        ];
    }

    protected function messages(): array
    {
        return [
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
        ];
    }

    public function mount(CartService $cartService): void
    {
        $cart = $cartService->current();

        if (! $cart || $cart->isEmpty()) {
            $this->redirectRoute('cart.index', navigate: false);
        }
    }

    public function goToAddress(): void
    {
        $this->validate([
            'name' => $this->rules()['name'],
            'mobile' => $this->rules()['mobile'],
            'email' => $this->rules()['email'],
        ]);

        $this->step = 2;
    }

    public function backToContact(): void
    {
        $this->step = 1;
    }

    public function goToPayment(): void
    {
        $this->validate([
            'line1' => $this->rules()['line1'],
            'line2' => $this->rules()['line2'],
            'city' => $this->rules()['city'],
            'pincode' => $this->rules()['pincode'],
        ]);

        $result = $this->resolvePincode($this->pincode);

        if ($result['error']) {
            $this->addError('pincode', $result['error']);

            return;
        }

        /** @var ShippingZone $zone */
        $zone = $result['zone'];

        $cart = app(CartService::class)->current();
        $subtotal = $cart?->total ?? Money::zero();

        $this->shippingChargePaise = $zone->chargeFor($subtotal)->paise();
        $this->codAvailable = $zone->cod_available;
        $this->state = $zone->name;

        if (! $this->codAvailable && $this->paymentMethod === 'cod') {
            $this->paymentMethod = 'razorpay';
        }

        $this->step = 3;
    }

    public function backToAddress(): void
    {
        $this->step = 2;
    }

    protected function throttleKey(): string
    {
        return 'checkout:'.request()->ip();
    }

    protected function tooManyAttempts(): bool
    {
        return RateLimiter::tooManyAttempts($this->throttleKey(), 5);
    }

    public function placeOrder(CartService $cartService, OrderService $orderService, RazorpayService $razorpayService): void
    {
        $this->validate(['paymentMethod' => $this->rules()['paymentMethod']]);

        if ($this->tooManyAttempts()) {
            $seconds = RateLimiter::availableIn($this->throttleKey());
            $this->addError('form', 'Too many attempts. Please try again in '.ceil($seconds / 60).' minute(s).');

            return;
        }

        RateLimiter::hit($this->throttleKey(), 3600);

        $cart = $cartService->current();

        if (! $cart || $cart->isEmpty()) {
            $this->addError('form', 'Your cart is empty.');

            return;
        }

        $zoneResult = $this->resolvePincode($this->pincode);

        if (! $zoneResult['zone']) {
            $this->addError('pincode', $zoneResult['error'] ?? 'Delivery not available to this pincode.');
            $this->step = 2;

            return;
        }

        $paymentMethod = PaymentMethod::from($this->paymentMethod);

        if ($paymentMethod === PaymentMethod::Cod && ! $zoneResult['zone']->cod_available) {
            $this->addError('form', 'Cash on Delivery is not available for this pincode.');

            return;
        }

        try {
            $order = $orderService->createPendingOrder(
                cart: $cart,
                contact: ['name' => $this->name, 'mobile' => $this->mobile, 'email' => $this->email ?: null],
                shippingAddress: [
                    'line1' => $this->line1,
                    'line2' => $this->line2 ?: null,
                    'city' => $this->city,
                    'pincode' => $this->pincode,
                    'state' => $zoneResult['zone']->name,
                ],
                zone: $zoneResult['zone'],
                paymentMethod: $paymentMethod,
            );
        } catch (CartValidationException $e) {
            $this->cartIssues = $e->issues();
            $this->step = 3;

            return;
        }

        if ($paymentMethod === PaymentMethod::Cod) {
            $this->orderNumber = $order->order_number;
            $this->redirect(
                URL::signedRoute('orders.success', ['order' => $order->order_number]),
                navigate: false,
            );

            return;
        }

        // Razorpay: create the gateway order and hand off to Checkout.js in
        // the browser via a dispatched event; server-side confirmation
        // happens in confirmRazorpayPayment() below.
        $this->orderNumber = $order->order_number;

        $razorpayOrder = $razorpayService->createOrder($order);

        $this->razorpayOrderId = $razorpayOrder['id'];
        $this->razorpayKey = $razorpayOrder['key'];
        $this->razorpayAmountPaise = $razorpayOrder['amount'];

        $this->dispatch(
            'razorpay-checkout-open',
            orderId: $razorpayOrder['id'],
            amount: $razorpayOrder['amount'],
            key: $razorpayOrder['key'],
            name: 'Nature Care Products',
            prefillName: $this->name,
            prefillEmail: $this->email,
            prefillContact: $this->mobile,
        );
    }

    public function razorpayCancelled(): void
    {
        $this->addError('form', 'Payment was cancelled. You can try again or choose Cash on Delivery.');
    }

    public function razorpayFailed(string $reason): void
    {
        $this->addError('form', 'Payment failed: '.$reason.' Please try again or choose Cash on Delivery.');
    }

    public function confirmRazorpayPayment(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature, OrderService $orderService, RazorpayService $razorpayService): void
    {
        if (! $razorpayService->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
            $this->addError('form', 'Payment verification failed. Please contact support if you were charged.');
            $this->dispatch('razorpay-payment-verification-failed');

            return;
        }

        $order = Order::query()->where('razorpay_order_id', $razorpayOrderId)->firstOrFail();

        $orderService->markPaid($order, $razorpayPaymentId, $razorpaySignature);

        $this->redirect(
            \URL::signedRoute('orders.success', ['order' => $order->order_number]),
            navigate: false,
        );
    }

    public function render()
    {
        $cart = app(CartService::class)->current();

        return view('livewire.checkout', [
            'cart' => $cart,
            'items' => $cart?->items ?? collect(),
        ]);
    }
}
