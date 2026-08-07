<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Idempotent backup to the Checkout.js success callback: on
 * `payment.captured`, calls the same OrderService::markPaid() the JS
 * callback uses, which is itself a no-op if the order is already paid.
 */
class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayService $razorpayService, OrderService $orderService): Response
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if ($signature === '' || ! $razorpayService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('RazorpayWebhookController: invalid signature');

            return response('Invalid signature', 400);
        }

        $data = json_decode($payload, true) ?? [];
        $event = $data['event'] ?? null;

        if ($event !== 'payment.captured') {
            // Acknowledge but ignore events we don't act on.
            return response('OK', 200);
        }

        $paymentEntity = $data['payload']['payment']['entity'] ?? [];
        $razorpayOrderId = $paymentEntity['order_id'] ?? null;
        $razorpayPaymentId = $paymentEntity['id'] ?? null;

        if (! $razorpayOrderId || ! $razorpayPaymentId) {
            return response('Missing payment data', 400);
        }

        $order = Order::query()->where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $order) {
            Log::warning('RazorpayWebhookController: no matching order', ['razorpay_order_id' => $razorpayOrderId]);

            return response('Order not found', 404);
        }

        // The webhook doesn't carry a checkout-signature (order_id|payment_id
        // signed with the key secret) — that's only produced by Checkout.js.
        // Authenticity here comes from the already-verified webhook
        // signature above, so we pass the webhook's own signature through
        // for audit purposes rather than re-deriving a checkout signature.
        $orderService->markPaid($order, $razorpayPaymentId, $signature);

        return response('OK', 200);
    }
}
