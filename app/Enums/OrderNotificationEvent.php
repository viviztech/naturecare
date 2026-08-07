<?php

namespace App\Enums;

enum OrderNotificationEvent: string
{
    case Placed = 'placed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case AdminNewOrder = 'admin_new_order';
}
