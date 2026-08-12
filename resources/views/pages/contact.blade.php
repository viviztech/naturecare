@php
    $phone = \App\Models\Setting::get('site_phone', '+91 99999 99999');
    $whatsapp = \App\Models\Setting::whatsappNumber();
    $email = \App\Models\Setting::get('site_email', 'enquiry@naturecareplus.com');
    $address = \App\Models\Setting::get('site_address', 'Chennai, Tamil Nadu, India');
    $mapUrl = \App\Models\Setting::get('google_map_embed_url', 'https://www.google.com/maps?q=Chennai,Tamil+Nadu&output=embed');
@endphp

<x-layouts.app
    :meta-title="'Contact Us | Nature Care Products'"
    :meta-description="'Get in touch with Nature Care Products. Call, email, or chat with us on WhatsApp for product and partnership enquiries.'"
>
    <section class="bg-brand-50">
        <div class="mx-auto max-w-4xl px-4 py-14 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-brand-900 sm:text-4xl">Contact Us</h1>
            <p class="mt-4 text-gray-600">We'd love to hear from you. Reach out with any questions.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <livewire:contact-form />
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold text-brand-800">Get in Touch</h2>
                    <ul class="mt-4 space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-3">
                            <span>📍</span>
                            <span>{{ $address }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span>📞</span>
                            <a href="tel:{{ $phone }}" class="hover:text-brand-700">{{ $phone }}</a>
                        </li>
                        <li class="flex items-start gap-3">
                            <span>✉️</span>
                            <a href="mailto:{{ $email }}" class="hover:text-brand-700">{{ $email }}</a>
                        </li>
                    </ul>
                    <a
                        href="https://wa.me/{{ $whatsapp }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700"
                    >
                        Chat on WhatsApp
                    </a>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                    <iframe
                        src="{{ $mapUrl }}"
                        width="100%"
                        height="300"
                        style="border:0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Nature Care Products location"
                    ></iframe>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
