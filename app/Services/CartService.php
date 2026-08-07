<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Support\Money;

/**
 * DB-backed cart, keyed by Laravel's own session id. Bound as a singleton in
 * AppServiceProvider so a single request only ever resolves/creates the
 * cart once.
 */
class CartService
{
    protected ?Cart $cart = null;

    public function current(): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        return $this->cart = Cart::query()
            ->with(['items.variant.product'])
            ->where('session_id', session()->getId())
            ->first();
    }

    public function currentOrCreate(): Cart
    {
        if ($cart = $this->current()) {
            return $cart;
        }

        $this->cart = Cart::query()->create([
            'session_id' => session()->getId(),
        ]);

        return $this->cart->load(['items.variant.product']);
    }

    public function addItem(ProductVariant $variant, int $qty = 1): void
    {
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = $this->currentOrCreate();

        $item = $cart->items()->where('product_variant_id', $variant->id)->first();

        if ($item) {
            $item->update(['qty' => $item->qty + $qty, 'unit_price' => $variant->selling_price]);
        } else {
            $cart->items()->create([
                'product_variant_id' => $variant->id,
                'qty' => $qty,
                'unit_price' => $variant->selling_price,
            ]);
        }

        $this->cart = null;
        $this->recalculate();
    }

    public function updateQty(int $cartItemId, int $qty): void
    {
        $cart = $this->currentOrCreate();

        $item = $cart->items()->whereKey($cartItemId)->first();

        if (! $item) {
            return;
        }

        if ($qty < 1) {
            $item->delete();
        } else {
            $item->update(['qty' => $qty]);
        }

        $this->cart = null;
        $this->recalculate();
    }

    public function removeItem(int $cartItemId): void
    {
        $cart = $this->currentOrCreate();

        $cart->items()->whereKey($cartItemId)->delete();

        $this->cart = null;
        $this->recalculate();
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function applyCoupon(string $code): array
    {
        $cart = $this->currentOrCreate();

        $coupon = Coupon::query()->where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'This coupon code does not exist.'];
        }

        $subtotal = $this->subtotal($cart);

        if ($error = $coupon->validationError($subtotal)) {
            return ['success' => false, 'message' => $error];
        }

        $cart->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
        ]);

        $this->cart = null;
        $this->recalculate();

        return ['success' => true, 'message' => "Coupon \"{$coupon->code}\" applied."];
    }

    public function removeCoupon(): void
    {
        $cart = $this->currentOrCreate();

        $cart->update(['coupon_id' => null, 'coupon_code' => null]);

        $this->cart = null;
        $this->recalculate();
    }

    public function count(): int
    {
        $cart = $this->current();

        if (! $cart) {
            return 0;
        }

        return (int) $cart->items()->sum('qty');
    }

    /**
     * Recalculates subtotal/discount/total from live cart_items + coupon.
     * Deliberately does NOT include shipping — shipping is only locked in
     * at checkout once a pincode resolves a ShippingZone, so a stale cached
     * shipping charge never survives a zone price change.
     */
    public function recalculate(): void
    {
        $cart = $this->currentOrCreate();
        $cart->load('items');

        $subtotal = $this->subtotal($cart);

        $discount = Money::zero();

        if ($cart->coupon_id && $coupon = Coupon::query()->find($cart->coupon_id)) {
            if ($coupon->validationError($subtotal) === null) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                // Coupon became invalid (e.g. cart dropped below min value) —
                // silently detach rather than leave a stale discount applied.
                $cart->coupon_id = null;
                $cart->coupon_code = null;
            }
        }

        $total = $subtotal->subtract($discount);

        $cart->subtotal = $subtotal;
        $cart->discount_total = $discount;
        $cart->total = $total;
        $cart->save();

        $this->cart = null;
    }

    protected function subtotal(Cart $cart): Money
    {
        return $cart->items->reduce(
            fn (Money $carry, CartItem $item) => $carry->add($item->lineTotal()),
            Money::zero(),
        );
    }

    public function clear(): void
    {
        $cart = $this->current();

        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        $this->cart = null;
    }
}
