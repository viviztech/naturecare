<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function webhookSecret(): string
    {
        return 'test-webhook-secret';
    }

    protected function signedPost(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        config(['services.razorpay.webhook_secret' => $this->webhookSecret()]);

        $body = json_encode($payload);
        $signature ??= hash_hmac('sha256', $body, $this->webhookSecret());

        return $this->call(
            'POST',
            '/webhooks/razorpay',
            [],
            [],
            [],
            ['HTTP_X-Razorpay-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $body,
        );
    }

    protected function capturedPayload(string $orderId, string $paymentId): array
    {
        return [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                        'status' => 'captured',
                    ],
                ],
            ],
        ];
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $order = Order::factory()->razorpayPending()->create(['razorpay_order_id' => 'order_abc']);

        $response = $this->signedPost($this->capturedPayload('order_abc', 'pay_123'), signature: 'not-a-real-signature');

        $response->assertStatus(400);
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_valid_signature_marks_order_paid_and_decrements_stock(): void
    {
        Mail::fake();

        $category = \App\Models\Category::factory()->create();
        $product = \App\Models\Product::factory()->for($category)->create();
        $variant = \App\Models\ProductVariant::factory()->for($product)->create(['stock_qty' => 10]);

        $order = Order::factory()->razorpayPending()->create(['razorpay_order_id' => 'order_abc']);
        $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name_snapshot' => $product->name,
            'size_label_snapshot' => $variant->size_label,
            'sku_snapshot' => $variant->sku,
            'qty' => 2,
            'unit_price' => 20000,
            'line_total' => 40000,
        ]);

        $response = $this->signedPost($this->capturedPayload('order_abc', 'pay_123'));

        $response->assertOk();

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Placed, $order->order_status);
        $this->assertNotNull($order->stock_decremented_at);
        $this->assertSame(8, $variant->fresh()->stock_qty);
    }

    public function test_webhook_is_idempotent_when_order_already_paid(): void
    {
        Mail::fake();

        $category = \App\Models\Category::factory()->create();
        $product = \App\Models\Product::factory()->for($category)->create();
        $variant = \App\Models\ProductVariant::factory()->for($product)->create(['stock_qty' => 10]);

        $order = Order::factory()->razorpayPending()->create(['razorpay_order_id' => 'order_abc']);
        $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name_snapshot' => $product->name,
            'size_label_snapshot' => $variant->size_label,
            'sku_snapshot' => $variant->sku,
            'qty' => 2,
            'unit_price' => 20000,
            'line_total' => 40000,
        ]);

        // First delivery of the webhook (simulates the JS callback already having marked it paid).
        $this->signedPost($this->capturedPayload('order_abc', 'pay_123'))->assertOk();
        $this->assertSame(8, $variant->fresh()->stock_qty);

        // Second delivery (Razorpay's real retry behaviour, or JS-callback + webhook both firing).
        $this->signedPost($this->capturedPayload('order_abc', 'pay_123'))->assertOk();

        // Stock must not be decremented twice.
        $this->assertSame(8, $variant->fresh()->stock_qty);
    }

    public function test_unhandled_event_types_are_acknowledged_but_ignored(): void
    {
        $order = Order::factory()->razorpayPending()->create(['razorpay_order_id' => 'order_abc']);

        $response = $this->signedPost([
            'event' => 'payment.failed',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_123', 'order_id' => 'order_abc']]],
        ]);

        $response->assertOk();
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_missing_order_returns_404(): void
    {
        $response = $this->signedPost($this->capturedPayload('order_does_not_exist', 'pay_123'));

        $response->assertStatus(404);
    }
}
