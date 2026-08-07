<?php

namespace App\Services\Notifications;

use App\Enums\OrderNotificationEvent;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Fans an order lifecycle event out to every configured channel. Currently
 * only Mail; adding WhatsApp Cloud API later means implementing
 * OrderNotificationChannel and appending it to the array passed in here —
 * zero changes to OrderService or Filament call sites.
 */
class OrderNotificationService
{
    /**
     * @param  array<int, OrderNotificationChannel>  $channels
     */
    public function __construct(
        protected array $channels,
    ) {}

    public function notify(Order $order, OrderNotificationEvent $event): void
    {
        foreach ($this->channels as $channel) {
            try {
                $channel->send($order, $event);
            } catch (Throwable $e) {
                // One broken channel must never block another, or block
                // the order-status transition that triggered this.
                Log::error('OrderNotificationService: channel failed', [
                    'channel' => $channel::class,
                    'order_id' => $order->id,
                    'event' => $event->value,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }
}
