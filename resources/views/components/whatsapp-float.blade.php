@php
    $whatsapp = \App\Models\Setting::whatsappNumber();
    $message = rawurlencode("Hi Nature Care, I'd like to know more about your products.");
@endphp

<a
    href="https://wa.me/{{ $whatsapp }}?text={{ $message }}"
    target="_blank"
    rel="noopener"
    aria-label="Chat on WhatsApp"
    class="whatsapp-float fixed bottom-20 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg transition hover:bg-brand-700 lg:bottom-5"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.14c-.24.68-1.4 1.32-1.93 1.4-.5.08-1.12.11-1.8-.11-.42-.13-.96-.3-1.65-.6-2.9-1.25-4.8-4.17-4.94-4.36-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.02-2.41.27-.29.58-.36.78-.36h.55c.18 0 .42-.07.65.5.24.58.8 2 .87 2.15.07.15.12.32.02.51-.1.19-.15.31-.29.48-.15.17-.31.38-.44.51-.15.15-.3.31-.13.6.17.29.76 1.25 1.63 2.02 1.12 1 2.06 1.31 2.36 1.46.29.15.46.13.63-.08.17-.2.72-.83.91-1.12.19-.29.38-.24.63-.15.25.1 1.6.76 1.88.9.27.14.46.2.53.32.07.11.07.66-.17 1.34Z"/>
    </svg>
</a>
