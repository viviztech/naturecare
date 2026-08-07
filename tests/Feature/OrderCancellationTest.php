<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrderWithItem(bool $stockDecremented): array
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock_qty' => 5]);

        $order = Order::factory()->create([
            'order_status' => OrderStatus::Placed,
            'stock_decremented_at' => $stockDecremented ? now() : null,
        ]);

        $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name_snapshot' => $product->name,
            'size_label_snapshot' => $variant->size_label,
            'sku_snapshot' => $variant->sku,
            'qty' => 3,
            'unit_price' => 20000,
            'line_total' => 60000,
        ]);

        return [$order, $variant];
    }

    public function test_cancelling_an_order_that_decremented_stock_restores_it(): void
    {
        [$order, $variant] = $this->makeOrderWithItem(stockDecremented: true);

        app(OrderService::class)->cancel($order, 'Customer requested cancellation');

        $this->assertSame(8, $variant->fresh()->stock_qty); // 5 + 3 restored
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->order_status);
        $this->assertNotNull($order->fresh()->cancelled_at);
        $this->assertSame('Customer requested cancellation', $order->fresh()->cancellation_reason);
    }

    public function test_cancelling_a_never_paid_razorpay_order_does_not_restore_stock_it_never_took(): void
    {
        // A Razorpay order that was never paid never had stock decremented
        // (stock_decremented_at is null) — cancelling it must not add stock
        // back that was never removed.
        [$order, $variant] = $this->makeOrderWithItem(stockDecremented: false);

        app(OrderService::class)->cancel($order, 'Payment never completed');

        $this->assertSame(5, $variant->fresh()->stock_qty); // unchanged
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->order_status);
    }

    public function test_cancelled_order_is_no_longer_cancellable(): void
    {
        [$order] = $this->makeOrderWithItem(stockDecremented: true);

        app(OrderService::class)->cancel($order);

        $this->assertFalse($order->fresh()->isCancellable());
    }

    public function test_delivered_order_is_not_cancellable(): void
    {
        [$order] = $this->makeOrderWithItem(stockDecremented: true);
        $order->update(['order_status' => OrderStatus::Delivered]);

        $this->assertFalse($order->fresh()->isCancellable());
    }
}
