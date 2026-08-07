<?php

namespace App\Services\Notifications;

use App\Enums\OrderNotificationEvent;
use App\Mail\AdminNewOrderAlert;
use App\Mail\OrderDelivered;
use App\Mail\OrderPlaced;
use App\Mail\OrderShipped;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class MailOrderNotificationChannel implements OrderNotificationChannel
{
    public function send(Order $order, OrderNotificationEvent $event): void
    {
        match ($event) {
            OrderNotificationEvent::Placed => $this->sendToCustomer($order, new OrderPlaced($order)),
            OrderNotificationEvent::Shipped => $this->sendToCustomer($order, new OrderShipped($order)),
            OrderNotificationEvent::Delivered => $this->sendToCustomer($order, new OrderDelivered($order)),
            OrderNotificationEvent::AdminNewOrder => $this->sendToAdmin($order),
        };
    }

    protected function sendToCustomer(Order $order, \Illuminate\Mail\Mailable $mailable): void
    {
        // COD orders often have no email — skip rather than error.
        if (blank($order->contact_email)) {
            return;
        }

        Mail::to($order->contact_email)->queue($mailable);
    }

    protected function sendToAdmin(Order $order): void
    {
        $adminEmail = Setting::get('admin_notification_email', config('naturecare.admin_notification_email'));

        Mail::to($adminEmail)->queue(new AdminNewOrderAlert($order));
    }
}
