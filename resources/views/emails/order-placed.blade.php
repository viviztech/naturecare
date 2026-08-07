@extends('emails.layout')

@section('title', 'Order Confirmed')

@section('content')
    <h2 style="margin-top:0;color:#245a5b;">Thank you for your order, {{ $order->contact_name }}!</h2>
    <p>Your order <strong>{{ $order->order_number }}</strong> has been placed successfully. Here's a summary:</p>

    <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        <tr>
            <th align="left" style="color:#245a5b;border-bottom:1px solid #dff5f6;">Item</th>
            <th align="left" style="color:#245a5b;border-bottom:1px solid #dff5f6;">Size</th>
            <th align="right" style="color:#245a5b;border-bottom:1px solid #dff5f6;">Qty</th>
            <th align="right" style="color:#245a5b;border-bottom:1px solid #dff5f6;">Amount</th>
        </tr>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name_snapshot }}</td>
                <td>{{ $item->size_label_snapshot }}</td>
                <td align="right">{{ $item->qty }}</td>
                <td align="right">{{ $item->line_total->format() }}</td>
            </tr>
        @endforeach
    </table>

    <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;margin-top:12px;">
        <tr><td style="color:#245a5b;"><strong>Subtotal</strong></td><td align="right">{{ $order->subtotal->format() }}</td></tr>
        @if (! $order->discount_total->isZero())
            <tr><td style="color:#245a5b;"><strong>Discount</strong></td><td align="right">-{{ $order->discount_total->format() }}</td></tr>
        @endif
        <tr><td style="color:#245a5b;"><strong>Shipping</strong></td><td align="right">{{ $order->shipping_charge->format() }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Tax (GST)</strong></td><td align="right">{{ $order->totalTax()->format() }}</td></tr>
        <tr><td style="color:#245a5b;border-top:1px solid #dff5f6;"><strong>Total</strong></td><td align="right" style="border-top:1px solid #dff5f6;"><strong>{{ $order->total->format() }}</strong></td></tr>
    </table>

    <p style="margin-top:20px;">
        <strong>Payment method:</strong> {{ $order->payment_method->label() }}<br>
        <strong>Delivery address:</strong> {{ $order->shipping_address['line1'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}
    </p>

    <p>We'll notify you again once your order ships. You can track it anytime using your mobile number and order number.</p>
@endsection
