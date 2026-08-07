@extends('emails.layout')

@section('title', 'New Business Enquiry')

@section('content')
    <h2 style="margin-top:0;color:#245a5b;">New {{ $enquiry->partner_type->label() }} Enquiry</h2>
    <p>A new business enquiry has been submitted on naturecareplus.com. Details below:</p>

    <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        <tr><td style="color:#245a5b;width:180px;"><strong>Partner Type</strong></td><td>{{ $enquiry->partner_type->label() }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Name</strong></td><td>{{ $enquiry->name }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Firm / Shop Name</strong></td><td>{{ $enquiry->firm_name }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Mobile (WhatsApp)</strong></td><td>{{ $enquiry->mobile }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Email</strong></td><td>{{ $enquiry->email ?: '—' }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Location</strong></td><td>{{ $enquiry->city }}, {{ $enquiry->district }}, {{ $enquiry->state }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Investment Capacity</strong></td><td>{{ config('naturecare.investment_ranges')[$enquiry->investment_range] ?? $enquiry->investment_range }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Years in Business</strong></td><td>{{ config('naturecare.years_in_business_ranges')[$enquiry->years_in_business] ?? $enquiry->years_in_business }}</td></tr>
        <tr><td style="color:#245a5b;"><strong>Current Business</strong></td><td>{{ config('naturecare.business_types')[$enquiry->current_business] ?? $enquiry->current_business }}</td></tr>
        @if (! is_null($enquiry->has_godown))
            <tr><td style="color:#245a5b;"><strong>Godown Available</strong></td><td>{{ $enquiry->has_godown ? 'Yes' : 'No' }}</td></tr>
        @endif
        @if ($enquiry->message)
            <tr><td style="color:#245a5b;vertical-align:top;"><strong>Message</strong></td><td>{{ $enquiry->message }}</td></tr>
        @endif
    </table>

    <p style="margin-top:24px;">
        <a href="https://wa.me/91{{ $enquiry->mobile }}" style="background-color:#3e999a;color:#ffffff;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;">
            Chat on WhatsApp
        </a>
    </p>
@endsection
