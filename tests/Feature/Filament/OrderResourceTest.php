<?php

namespace Tests\Feature\Filament;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_orders(): void
    {
        $this->get('/admin/orders')->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_view_orders_index(): void
    {
        $this->actingAs(User::factory()->create());

        Order::factory()->count(2)->create();

        $this->get('/admin/orders')->assertOk();
    }

    public function test_authenticated_admin_can_view_a_single_order(): void
    {
        $this->actingAs(User::factory()->create());

        $order = Order::factory()->create();

        $this->get("/admin/orders/{$order->order_number}")->assertOk();
    }

    public function test_authenticated_admin_can_view_coupons(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/coupons')->assertOk();
        $this->get('/admin/coupons/create')->assertOk();
    }

    public function test_authenticated_admin_can_view_shipping_zones(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/shipping-zones')->assertOk();
        $this->get('/admin/shipping-zones/create')->assertOk();
    }

    public function test_authenticated_admin_can_download_gst_invoice(): void
    {
        $this->actingAs(User::factory()->create());

        $order = Order::factory()->create();

        $response = $this->get(route('admin.orders.invoice', $order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertNotNull($order->fresh()->gst_invoice_number);
    }

    public function test_admin_can_mark_a_cod_order_as_paid_via_the_view_page_action(): void
    {
        $this->actingAs(User::factory()->create());

        $order = Order::factory()->create(['payment_status' => PaymentStatus::Pending]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('markCodPaid');

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
    }

    public function test_admin_can_update_order_status_via_the_view_page_action(): void
    {
        $this->actingAs(User::factory()->create());

        $order = Order::factory()->create(['order_status' => OrderStatus::Placed]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('updateStatus', data: ['order_status' => OrderStatus::Shipped->value]);

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->order_status);
    }

    public function test_admin_can_add_tracking_via_the_view_page_action(): void
    {
        $this->actingAs(User::factory()->create());

        $order = Order::factory()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('addTracking', data: [
                'tracking_number' => 'TRACK123',
                'tracking_url' => 'https://example.com/track/TRACK123',
            ]);

        $order->refresh();
        $this->assertSame('TRACK123', $order->tracking_number);
        $this->assertSame('https://example.com/track/TRACK123', $order->tracking_url);
    }

    public function test_admin_can_cancel_a_cancellable_order_via_the_view_page_action(): void
    {
        $this->actingAs(User::factory()->create());

        $order = Order::factory()->create(['order_status' => OrderStatus::Placed]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('cancel', data: ['reason' => 'Customer changed their mind']);

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->order_status);
        $this->assertSame('Customer changed their mind', $order->cancellation_reason);
    }
}
