<?php

namespace Tests\Feature;

use App\Livewire\OrderTracking;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_page_loads(): void
    {
        $this->get(route('order.track'))->assertOk();
    }

    public function test_finds_order_matching_mobile_and_order_number(): void
    {
        RateLimiter::clear('order-track:127.0.0.1');

        $order = Order::factory()->create([
            'contact_mobile' => '9876543210',
            'order_number' => 'NC-202608-0001',
        ]);

        Livewire::test(OrderTracking::class)
            ->set('mobile', '9876543210')
            ->set('orderNumber', 'NC-202608-0001')
            ->call('track')
            ->assertHasNoErrors()
            ->assertSee($order->order_number);
    }

    public function test_mismatched_mobile_and_order_number_shows_error(): void
    {
        RateLimiter::clear('order-track:127.0.0.1');

        Order::factory()->create([
            'contact_mobile' => '9876543210',
            'order_number' => 'NC-202608-0001',
        ]);

        Livewire::test(OrderTracking::class)
            ->set('mobile', '9999999999')
            ->set('orderNumber', 'NC-202608-0001')
            ->call('track')
            ->assertHasErrors(['form']);
    }

    public function test_track_is_rate_limited(): void
    {
        RateLimiter::clear('order-track:127.0.0.1');

        Order::factory()->create(['contact_mobile' => '9876543210', 'order_number' => 'NC-202608-0001']);

        for ($i = 0; $i < 10; $i++) {
            Livewire::test(OrderTracking::class)
                ->set('mobile', '9876543210')
                ->set('orderNumber', 'NC-202608-0001')
                ->call('track');
        }

        Livewire::test(OrderTracking::class)
            ->set('mobile', '9876543210')
            ->set('orderNumber', 'NC-202608-0001')
            ->call('track')
            ->assertHasErrors(['form']);
    }
}
