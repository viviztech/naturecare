<?php

namespace App\Services;

use App\Enums\OrderNotificationEvent;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\CartValidationException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Services\Notifications\OrderNotificationService;
use App\Support\Money;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected SequenceGenerator $sequenceGenerator,
        protected GstService $gstService,
        protected CartService $cartService,
        protected OrderNotificationService $notificationService,
    ) {}

    /**
     * Validates the cart's live state against current variant stock/price,
     * creates (or reuses) the guest Customer record, creates the Order +
     * OrderItems, and — for COD only — decrements stock and marks the
     * order Placed immediately (Razorpay orders stay PendingPayment with
     * stock untouched until OrderService::markPaid() runs).
     *
     * @param  array{name: string, mobile: string, email: ?string}  $contact
     * @param  array{line1: string, line2: ?string, city: string, pincode: string}  $shippingAddress
     *
     * @throws CartValidationException
     */
    public function createPendingOrder(
        Cart $cart,
        array $contact,
        array $shippingAddress,
        ShippingZone $zone,
        PaymentMethod $paymentMethod,
    ): Order {
        $cart->load('items.variant.product');

        if ($cart->items->isEmpty()) {
            throw new CartValidationException(['Your cart is empty.']);
        }

        $this->assertCartStillValid($cart);

        return $this->retryOnceOnUniqueViolation(function () use ($cart, $contact, $shippingAddress, $zone, $paymentMethod) {
            return DB::transaction(function () use ($cart, $contact, $shippingAddress, $zone, $paymentMethod) {
                $customer = Customer::query()->updateOrCreate(
                    ['mobile' => $contact['mobile']],
                    ['name' => $contact['name'], 'email' => $contact['email'] ?? null],
                );

                $subtotal = $this->cartSubtotal($cart);
                $discount = $this->cartDiscount($cart, $subtotal);
                $taxableAmount = $subtotal->subtract($discount);
                $shippingCharge = $zone->chargeFor($taxableAmount);
                $gst = $this->gstService->calculateForOrder($taxableAmount, $shippingAddress['state'] ?? null);

                // GST is calculated on the taxable (post-discount) goods
                // value; shipping charge itself is not taxed separately here.
                $total = $taxableAmount->add($shippingCharge);

                $isCod = $paymentMethod === PaymentMethod::Cod;

                $order = Order::query()->create([
                    'order_number' => $this->sequenceGenerator->nextOrderNumber(),
                    'customer_id' => $customer->id,
                    'contact_name' => $contact['name'],
                    'contact_mobile' => $contact['mobile'],
                    'contact_email' => $contact['email'] ?? null,
                    'shipping_address' => $shippingAddress,
                    'shipping_state' => $shippingAddress['state'] ?? null,
                    'shipping_pincode' => $shippingAddress['pincode'],
                    'cod_available_at_order' => $zone->cod_available,
                    'payment_method' => $paymentMethod,
                    'payment_status' => PaymentStatus::Pending,
                    'order_status' => $isCod ? OrderStatus::Placed : OrderStatus::PendingPayment,
                    'subtotal' => $subtotal,
                    'discount_total' => $discount,
                    'shipping_charge' => $shippingCharge,
                    'tax_cgst' => $gst['cgst'],
                    'tax_sgst' => $gst['sgst'],
                    'tax_igst' => $gst['igst'],
                    'total' => $total,
                    'coupon_id' => $cart->coupon_id,
                    'coupon_code' => $cart->coupon_code,
                    'stock_decremented_at' => $isCod ? now() : null,
                ]);

                foreach ($cart->items as $item) {
                    $variant = $item->variant;

                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'product_name_snapshot' => $variant->product->name,
                        'size_label_snapshot' => $variant->size_label,
                        'sku_snapshot' => $variant->sku,
                        'hsn_code_snapshot' => $variant->product->hsn_code,
                        'qty' => $item->qty,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->lineTotal(),
                    ]);

                    if ($isCod) {
                        $variant->decrement('stock_qty', $item->qty);
                    }
                }

                if ($cart->coupon_id && $coupon = Coupon::query()->find($cart->coupon_id)) {
                    $coupon->increment('used_count');
                }

                $this->cartService->clear();

                if ($isCod) {
                    $this->notificationService->notify($order, OrderNotificationEvent::Placed);
                    $this->notificationService->notify($order, OrderNotificationEvent::AdminNewOrder);
                }

                return $order->fresh(['items']);
            });
        });
    }

    /**
     * Marks a Razorpay order as paid. Called from both the JS success
     * callback and the webhook — idempotent (no-op if already paid), which
     * is what makes the two mutually safe backups of each other.
     */
    public function markPaid(Order $order, string $razorpayPaymentId, string $razorpaySignature): void
    {
        DB::transaction(function () use ($order, $razorpayPaymentId, $razorpaySignature) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status === PaymentStatus::Paid) {
                return;
            }

            $locked->update([
                'payment_status' => PaymentStatus::Paid,
                'order_status' => OrderStatus::Placed,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
                'stock_decremented_at' => now(),
            ]);

            foreach ($locked->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::query()->whereKey($item->product_variant_id)->decrement('stock_qty', $item->qty);
                }
            }

            $this->notificationService->notify($locked, OrderNotificationEvent::Placed);
            $this->notificationService->notify($locked, OrderNotificationEvent::AdminNewOrder);
        });
    }

    public function markCodPaid(Order $order): void
    {
        $order->update(['payment_status' => PaymentStatus::Paid]);
    }

    public function updateStatus(Order $order, OrderStatus $status): void
    {
        $order->update(['order_status' => $status]);

        match ($status) {
            OrderStatus::Shipped => $this->notificationService->notify($order, OrderNotificationEvent::Shipped),
            OrderStatus::Delivered => $this->notificationService->notify($order, OrderNotificationEvent::Delivered),
            default => null,
        };
    }

    public function addTracking(Order $order, string $trackingNumber, ?string $trackingUrl = null): void
    {
        $order->update([
            'tracking_number' => $trackingNumber,
            'tracking_url' => $trackingUrl,
        ]);
    }

    public function cancel(Order $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->stockWasDecremented()) {
                foreach ($locked->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::query()->whereKey($item->product_variant_id)->increment('stock_qty', $item->qty);
                    }
                }
            }

            $locked->update([
                'order_status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'stock_decremented_at' => null,
            ]);
        });
    }

    /**
     * Re-checks every cart item's variant against live stock/price,
     * collecting all issues so the checkout UI can show them at once.
     *
     * @throws CartValidationException
     */
    protected function assertCartStillValid(Cart $cart): void
    {
        $issues = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant()->first();

            if (! $variant || ! $variant->is_active) {
                $issues[] = "\"{$item->variant?->product?->name}\" is no longer available and was removed from your cart.";
                $item->delete();

                continue;
            }

            if ($variant->stock_qty < $item->qty) {
                $issues[] = "Only {$variant->stock_qty} unit(s) of \"{$variant->product->name} - {$variant->size_label}\" left in stock.";
            }

            if ($variant->selling_price->paise() !== $item->unit_price->paise()) {
                $item->update(['unit_price' => $variant->selling_price]);
                $issues[] = "The price of \"{$variant->product->name} - {$variant->size_label}\" has changed. Please review your cart.";
            }
        }

        if ($issues !== []) {
            $this->cartService->recalculate();

            throw new CartValidationException($issues);
        }
    }

    protected function cartSubtotal(Cart $cart): Money
    {
        return $cart->items->reduce(
            fn (Money $carry, $item) => $carry->add($item->lineTotal()),
            Money::zero(),
        );
    }

    protected function cartDiscount(Cart $cart, Money $subtotal): Money
    {
        if (! $cart->coupon_id) {
            return Money::zero();
        }

        $coupon = Coupon::query()->find($cart->coupon_id);

        if (! $coupon || $coupon->validationError($subtotal) !== null) {
            return Money::zero();
        }

        return $coupon->calculateDiscount($subtotal);
    }

    /**
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    protected function retryOnceOnUniqueViolation(\Closure $callback)
    {
        try {
            return $callback();
        } catch (QueryException $e) {
            $isUniqueViolation = str_contains($e->getMessage(), 'Unique') || (int) ($e->errorInfo[1] ?? 0) === 1062;

            if (! $isUniqueViolation) {
                throw $e;
            }

            return $callback();
        }
    }
}
