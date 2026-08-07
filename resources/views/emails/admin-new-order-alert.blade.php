@extends('emails.layout')

@section('title', 'New Order Received')

@section('content')
    <h2 style="margin-top:0;color:#245a5b;">New Order — {{ $order->order_number }}</h2>
    <p>A new order has been placed on naturecareplus.com.</p>

    <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        <tr><td style="color:#245a5b;width:180px;"><strong>Customer</strong></td><td>{{ $order->contact_name }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Mobile</strong></td><td>{{ $order->contact_mobile }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Payment Method</strong></td><td>{{ $order->payment_method->label() }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Payment Status</strong></td><td>{{ $order->payment_status->label() }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Items</strong></td><td>{{ $order->items->count() }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Total</strong></td><td>{{ $order->total->format() }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Delivery Location</strong></td><td>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</td></tr>
    </table>

    <p style="margin-top:24px;">
        <a href="{{ url('/admin/orders/'.$order->id) }}" style="background-color:#3e999a;color:#ffffff;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;">
            View Order in Admin
        </a>
    </p>
@endsection
