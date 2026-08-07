<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Checkout;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutRazorpayTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCartWithVariant(int $stock = 10): ProductVariant
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $variant = ProductVariant::factory()->for($product)->create([
            'stock_qty' => $stock,
            'selling_price' => 20000,
            'mrp' => 20000,
        ]);

        app(CartService::class)->addItem($variant, 2);

        return $variant;
    }

    protected function goToPaymentStep(): \Livewire\Features\SupportTesting\Testable
    {
        ShippingZone::factory()->create([
            'pincode_prefix' => '6',
            'name' => 'Tamil Nadu',
            'shipping_charge' => 4900,
            'cod_available' => true,
        ]);

        return Livewire::test(Checkout::class)
            ->set('name', 'Ramesh Kumar')
            ->set('mobile', '9876543210')
            ->set('email', 'ramesh@example.com')
            ->call('goToAddress')
            ->set('line1', '12 Anna Nagar')
            ->set('city', 'Chennai')
            ->set('pincode', '600001')
            ->call('goToPayment')
            ->set('paymentMethod', 'razorpay');
    }

    public function test_initiating_razorpay_payment_creates_pending_order_without_decrementing_stock(): void
    {
        Mail::fake();
        RateLimiter::clear('checkout:127.0.0.1');

        $variant = $this->seedCartWithVariant(10);

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('createOrder')
                ->once()
                ->andReturnUsing(function (Order $order) {
                    // Mirrors RazorpayService::createOrder()'s real side
                    // effect of persisting razorpay_order_id onto the order.
                    $order->update(['razorpay_order_id' => 'order_test123']);

                    return ['id' => 'order_test123', 'amount' => 44900, 'currency' => 'INR', 'key' => 'rzp_test_key'];
                });
        });

        $this->goToPaymentStep()
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertDispatched('razorpay-checkout-open');

        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::PendingPayment, $order->order_status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertNull($order->stock_decremented_at);
        $this->assertSame(10, $variant->fresh()->stock_qty);
        $this->assertSame('order_test123', $order->razorpay_order_id);
    }

    public function test_confirming_payment_with_valid_signature_marks_order_paid_and_decrements_stock(): void
    {
        Mail::fake();
        RateLimiter::clear('checkout:127.0.0.1');

        $variant = $this->seedCartWithVariant(10);

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('createOrder')
                ->once()
                ->andReturnUsing(function (Order $order) {
                    // Mirrors RazorpayService::createOrder()'s real side
                    // effect of persisting razorpay_order_id onto the order.
                    $order->update(['razorpay_order_id' => 'order_test123']);

                    return ['id' => 'order_test123', 'amount' => 44900, 'currency' => 'INR', 'key' => 'rzp_test_key'];
                });

            $mock->shouldReceive('verifySignature')
                ->once()
                ->with('order_test123', 'pay_test456', 'sig_test789')
                ->andReturn(true);
        });

        $component = $this->goToPaymentStep()->call('placeOrder');

        $component->call('confirmRazorpayPayment', 'order_test123', 'pay_test456', 'sig_test789')
            ->assertHasNoErrors();

        $order = Order::query()->first();

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Placed, $order->fresh()->order_status);
        $this->assertNotNull($order->fresh()->stock_decremented_at);
        $this->assertSame(8, $variant->fresh()->stock_qty);
    }

    public function test_confirming_payment_with_invalid_signature_does_not_mark_order_paid(): void
    {
        Mail::fake();
        RateLimiter::clear('checkout:127.0.0.1');

        $this->seedCartWithVariant(10);

        $this->mock(RazorpayService::class, function ($mock) {
            $mock->shouldReceive('createOrder')
                ->once()
                ->andReturnUsing(function (Order $order) {
                    // Mirrors RazorpayService::createOrder()'s real side
                    // effect of persisting razorpay_order_id onto the order.
                    $order->update(['razorpay_order_id' => 'order_test123']);

                    return ['id' => 'order_test123', 'amount' => 44900, 'currency' => 'INR', 'key' => 'rzp_test_key'];
                });

            $mock->shouldReceive('verifySignature')
                ->once()
                ->andReturn(false);
        });

        $component = $this->goToPaymentStep()->call('placeOrder');

        $component->call('confirmRazorpayPayment', 'order_test123', 'pay_bad', 'sig_bad')
            ->assertHasErrors(['form']);

        $order = Order::query()->first();

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }
}
