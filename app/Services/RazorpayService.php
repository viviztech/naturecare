<?php

namespace App\Services;

use App\Models\Order;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret'),
        );
    }

    /**
     * Creates a Razorpay order for the given (already persisted, pending)
     * local order, and persists the returned razorpay_order_id back onto it.
     *
     * @return array{id: string, amount: int, currency: string, key: ?string}
     */
    public function createOrder(Order $order): array
    {
        $razorpayOrder = $this->api->order->create([
            'amount' => $order->total->paise(),
            'currency' => 'INR',
            'receipt' => $order->order_number,
            'notes' => [
                'order_number' => $order->order_number,
            ],
        ]);

        $order->update(['razorpay_order_id' => $razorpayOrder['id']]);

        return [
            'id' => $razorpayOrder['id'],
            'amount' => $order->total->paise(),
            'currency' => 'INR',
            'key' => config('services.razorpay.key'),
        ];
    }

    /**
     * Verifies a Checkout.js success-callback payment signature.
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    /**
     * Verifies a webhook request's X-Razorpay-Signature header against the
     * raw request body using the shared webhook secret.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('services.razorpay.webhook_secret');

        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
