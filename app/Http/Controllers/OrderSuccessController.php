<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class OrderSuccessController extends Controller
{
    public function show(Order $order): View
    {
        $order->load('items');

        return view('pages.order-success', [
            'order' => $order,
        ]);
    }
}
