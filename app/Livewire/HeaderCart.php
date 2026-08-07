<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderCart extends Component
{
    public int $count = 0;

    public function mount(CartService $cartService): void
    {
        $this->count = $cartService->count();
    }

    #[On('cart-updated')]
    public function refreshCount(CartService $cartService): void
    {
        $this->count = $cartService->count();
    }

    public function render()
    {
        return view('livewire.header-cart');
    }
}
