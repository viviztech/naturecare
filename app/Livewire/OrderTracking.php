<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class OrderTracking extends Component
{
    public string $mobile = '';

    public string $orderNumber = '';

    public bool $searched = false;

    public ?int $orderId = null;

    public function mount(): void
    {
        // Prefilled from a signed order-success redirect, if present.
        if (request()->has('order_number')) {
            $this->orderNumber = (string) request()->query('order_number');
        }
    }

    public function track(): void
    {
        $this->validate([
            'mobile' => ['required', 'digits:10'],
            'orderNumber' => ['required', 'string'],
        ]);

        $throttleKey = 'order-track:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('form', 'Too many attempts. Please try again in '.ceil($seconds / 60).' minute(s).');

            return;
        }

        RateLimiter::hit($throttleKey, 3600);

        $this->searched = true;

        $order = Order::query()
            ->where('contact_mobile', $this->mobile)
            ->where('order_number', trim($this->orderNumber))
            ->first();

        $this->orderId = $order?->id;

        if (! $order) {
            $this->addError('form', 'No order found matching that mobile number and order number.');
        }
    }

    public function render()
    {
        $order = $this->orderId ? Order::query()->with('items')->find($this->orderId) : null;

        return view('livewire.order-tracking', [
            'order' => $order,
        ]);
    }
}
