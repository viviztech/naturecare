@extends('emails.layout')

@section('title', 'Order Shipped')

@section('content')
    <h2 style="margin-top:0;color:#245a5b;">Your order is on its way, {{ $order->contact_name }}!</h2>
    <p>Good news — order <strong>{{ $order->order_number }}</strong> has been shipped.</p>

    @if ($order->tracking_number)
        <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
            <tr><td style="color:#245a5b;width:180px;"><strong>Tracking Number</strong></td><td>{{ $order->tracking_number }}</td></tr>
            @if ($order->tracking_url)
                <tr><td style="color:#245a5b;"><strong>Track Shipment</strong></td><td><a href="{{ $order->tracking_url }}">{{ $order->tracking_url }}</a></td></tr>
            @endif
        </table>
    @endif

    <p style="margin-top:20px;">
        <strong>Delivery address:</strong> {{ $order->shipping_address['line1'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}
    </p>
@endsection
