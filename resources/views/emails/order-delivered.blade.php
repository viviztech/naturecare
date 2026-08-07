@extends('emails.layout')

@section('title', 'Order Delivered')

@section('content')
    <h2 style="margin-top:0;color:#245a5b;">Your order has been delivered!</h2>
    <p>Hi {{ $order->contact_name }}, order <strong>{{ $order->order_number }}</strong> has been marked as delivered. We hope you love your Nature Care products.</p>

    <p>If anything isn't right with your order, just reply to this email or reach out to us on WhatsApp and we'll sort it out.</p>

    <p style="margin-top:24px;">
        <a href="https://wa.me/{{ \App\Models\Setting::get('site_whatsapp', config('naturecare.whatsapp_number')) }}" style="background-color:#3e999a;color:#ffffff;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;">
            Chat on WhatsApp
        </a>
    </p>
@endsection
