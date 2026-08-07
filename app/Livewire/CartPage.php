<?php

namespace App\Livewire;

use App\Livewire\Concerns\ValidatesPincode;
use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    use ValidatesPincode;

    public string $couponCode = '';

    public ?string $couponMessage = null;

    public bool $couponSuccess = false;

    public string $pincode = '';

    public ?string $pincodeError = null;

    public ?int $shippingChargePaise = null;

    public ?bool $codAvailable = null;

    public function updateQty(int $itemId, int $qty): void
    {
        app(CartService::class)->updateQty($itemId, $qty);
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId): void
    {
        app(CartService::class)->removeItem($itemId);
        $this->dispatch('cart-updated');
    }

    public function applyCoupon(): void
    {
        $result = app(CartService::class)->applyCoupon($this->couponCode);

        $this->couponSuccess = $result['success'];
        $this->couponMessage = $result['message'];
        $this->dispatch('cart-updated');
    }

    public function removeCoupon(): void
    {
        app(CartService::class)->removeCoupon();
        $this->couponCode = '';
        $this->couponMessage = null;
        $this->dispatch('cart-updated');
    }

    public function checkPincode(): void
    {
        $result = $this->resolvePincode($this->pincode);

        if ($result['error']) {
            $this->pincodeError = $result['error'];
            $this->shippingChargePaise = null;
            $this->codAvailable = null;

            return;
        }

        $zone = $result['zone'];
        $cart = app(CartService::class)->current();
        $subtotal = $cart?->total ?? \App\Support\Money::zero();

        $this->pincodeError = null;
        $this->shippingChargePaise = $zone->chargeFor($subtotal)->paise();
        $this->codAvailable = $zone->cod_available;
    }

    public function render()
    {
        $cart = app(CartService::class)->current();

        return view('livewire.cart-page', [
            'cart' => $cart,
            'items' => $cart?->items ?? collect(),
        ]);
    }
}
