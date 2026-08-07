<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddToCart extends Component
{
    #[Locked]
    public int $productId;

    public ?int $variantId = null;

    public int $qty = 1;

    public bool $justAdded = false;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;

        $firstInStock = $product->activeVariants->first(fn (ProductVariant $v) => $v->isInStock());

        $this->variantId = ($firstInStock ?? $product->activeVariants->first())?->id;
    }

    public function selectVariant(int $variantId): void
    {
        $this->variantId = $variantId;
        $this->qty = 1;
        $this->justAdded = false;
    }

    public function incrementQty(): void
    {
        $variant = $this->selectedVariant();

        if ($variant && $this->qty < $variant->stock_qty) {
            $this->qty++;
        }
    }

    public function decrementQty(): void
    {
        if ($this->qty > 1) {
            $this->qty--;
        }
    }

    public function addToCart(CartService $cartService): void
    {
        $variant = $this->selectedVariant();

        if (! $variant || ! $variant->is_active) {
            $this->addError('variant', 'Please select a size.');

            return;
        }

        if (! $variant->isInStock()) {
            $this->addError('variant', 'This size is currently out of stock.');

            return;
        }

        $qty = min($this->qty, $variant->stock_qty);

        $cartService->addItem($variant, $qty);

        $this->justAdded = true;

        $this->dispatch('cart-updated');
    }

    protected function selectedVariant(): ?ProductVariant
    {
        if (! $this->variantId) {
            return null;
        }

        return ProductVariant::query()->find($this->variantId);
    }

    public function render()
    {
        $product = Product::query()->with('activeVariants')->findOrFail($this->productId);

        return view('livewire.add-to-cart', [
            'product' => $product,
            'variants' => $product->activeVariants,
        ]);
    }
}
