<?php

namespace App\Services\Notifications;

use App\Enums\OrderNotificationEvent;
use App\Models\Order;

interface OrderNotificationChannel
{
    public function send(Order $order, OrderNotificationEvent $event): void;
}
