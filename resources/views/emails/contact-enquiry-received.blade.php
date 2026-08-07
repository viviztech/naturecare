@extends('emails.layout')

@section('title', 'New Contact Enquiry')

@section('content')
    <h2 style="margin-top:0;color:#245a5b;">New Contact Enquiry</h2>
    <p>A new message has been submitted through the contact form on naturecareplus.com:</p>

    <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        <tr><td style="color:#245a5b;width:150px;"><strong>Name</strong></td><td>{{ $enquiry->name }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Mobile</strong></td><td>{{ $enquiry->mobile }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Email</strong></td><td>{{ $enquiry->email ?: '—' }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Subject</strong></td><td>{{ $enquiry->subject ?: '—' }}</td></tr>
        <tr><td style="color:#245a5b;vertical-align:top;"><strong>Message</strong></td><td>{{ $enquiry->message }}</td></tr>
    </table>

    <p style="margin-top:24px;">
        <a href="https://wa.me/91{{ $enquiry->mobile }}" style="background-color:#3e999a;color:#ffffff;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;">
            Chat on WhatsApp
        </a>
    </p>
@endsection
