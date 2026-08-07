<x-layouts.app
    :meta-title="'Thank You | Nature Care Products'"
    :meta-description="'Thank you for your partner enquiry with Nature Care Products.'"
>
    @push('scripts')
        @if (config('naturecare.meta_pixel_id'))
            <script>
                if (window.fbq) {
                    fbq('track', 'Lead', { content_name: 'partner_enquiry', partner_type: @json($partnerType?->value) });
                }
            </script>
        @endif
    @endpush

    <section class="mx-auto flex min-h-[60vh] max-w-2xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-600 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="mt-6 text-3xl font-extrabold text-brand-900">Thank You!</h1>
        <p class="mt-3 text-gray-600">
            @if ($partnerType)
                Your {{ $partnerType->label() }} enquiry has been received.
            @else
                Your enquiry has been received.
            @endif
            Our partnerships team will get back to you within {{ config('naturecare.expected_response_hours') }} hours.
        </p>
        <a href="{{ route('home') }}" class="mt-8 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700">
            Back to Home
        </a>
    </section>
</x-layouts.app>
