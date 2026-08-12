@php
    $phone = \App\Models\Setting::get('site_phone', '+91 99999 99999');
    $whatsapp = \App\Models\Setting::whatsappNumber();
    $email = \App\Models\Setting::get('site_email', 'enquiry@naturecareplus.com');
    $address = \App\Models\Setting::get('site_address', 'Chennai, Tamil Nadu, India');
    $socialLinks = [
        'Facebook' => \App\Models\Setting::get('facebook_url'),
        'Instagram' => \App\Models\Setting::get('instagram_url'),
        'YouTube' => \App\Models\Setting::get('youtube_url'),
    ];
@endphp

<footer class="bg-brand-900 text-brand-100">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <span class="inline-block rounded-lg bg-white px-3 py-2">
                    <img src="{{ asset('images/nature-care-logo.jpeg') }}" alt="Nature Care Products" width="374" height="96" class="h-8 w-auto">
                </span>
                <p class="mt-3 text-sm text-brand-200">Naturally Clean, Naturally Safe. Nature-inspired home &amp; personal care products made in India, since 2018.</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Quick Links</h3>
                <ul class="mt-3 space-y-2 text-sm text-brand-200">
                    <li><a href="{{ route('products.index') }}" class="hover:text-white">Products</a></li>
                    <li><a href="{{ route('partner.index') }}" class="hover:text-white">Become a Partner</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Categories</h3>
                <ul class="mt-3 space-y-2 text-sm text-brand-200">
                    @foreach (\App\Models\Category::query()->active()->ordered()->limit(5)->get() as $category)
                        <li><a href="{{ route('products.index', ['category' => $category->slug]) }}" class="hover:text-white">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Contact Us</h3>
                <ul class="mt-3 space-y-2 text-sm text-brand-200">
                    <li>{{ $address }}</li>
                    <li><a href="tel:{{ $phone }}" class="hover:text-white">{{ $phone }}</a></li>
                    <li><a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></li>
                    <li>
                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Z"/>
                            </svg>
                            Chat on WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-brand-700 pt-6 text-xs text-brand-300 sm:flex-row">
            <p>&copy; {{ date('Y') }} Nature Care Products. All rights reserved.</p>

            @if (array_filter($socialLinks))
                <div class="flex items-center gap-3">
                    @if ($socialLinks['Facebook'])
                        <a href="{{ $socialLinks['Facebook'] }}" target="_blank" rel="noopener" aria-label="Nature Care Products on Facebook" class="text-brand-300 transition hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95Z"/>
                            </svg>
                        </a>
                    @endif
                    @if ($socialLinks['Instagram'])
                        <a href="{{ $socialLinks['Instagram'] }}" target="_blank" rel="noopener" aria-label="Nature Care Products on Instagram" class="text-brand-300 transition hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2c-2.72 0-3.06.01-4.12.06-1.06.05-1.79.22-2.43.47-.66.26-1.22.6-1.77 1.16-.56.55-.9 1.11-1.16 1.77-.25.64-.42 1.37-.47 2.43C2 8.94 2 9.28 2 12s.01 3.06.06 4.12c.05 1.06.22 1.79.47 2.43.26.66.6 1.22 1.16 1.77.55.56 1.11.9 1.77 1.16.64.25 1.37.42 2.43.47C8.94 22 9.28 22 12 22s3.06-.01 4.12-.06c1.06-.05 1.79-.22 2.43-.47.66-.26 1.22-.6 1.77-1.16.56-.55.9-1.11 1.16-1.77.25-.64.42-1.37.47-2.43.05-1.06.06-1.4.06-4.12s-.01-3.06-.06-4.12c-.05-1.06-.22-1.79-.47-2.43a4.9 4.9 0 0 0-1.16-1.77 4.9 4.9 0 0 0-1.77-1.16c-.64-.25-1.37-.42-2.43-.47C15.06 2 14.72 2 12 2Zm0 1.8c2.67 0 2.99.01 4.04.06.98.04 1.5.21 1.86.34.47.18.8.4 1.15.75.35.35.57.68.75 1.15.13.36.29.88.34 1.86.05 1.05.06 1.37.06 4.04s-.01 2.99-.06 4.04c-.04.98-.21 1.5-.34 1.86-.18.47-.4.8-.75 1.15-.35.35-.68.57-1.15.75-.36.13-.88.29-1.86.34-1.05.05-1.37.06-4.04.06s-2.99-.01-4.04-.06c-.98-.04-1.5-.21-1.86-.34a3.1 3.1 0 0 1-1.15-.75 3.1 3.1 0 0 1-.75-1.15c-.13-.36-.29-.88-.34-1.86C3.81 14.99 3.8 14.67 3.8 12s.01-2.99.06-4.04c.04-.98.21-1.5.34-1.86.18-.47.4-.8.75-1.15.35-.35.68-.57 1.15-.75.36-.13.88-.29 1.86-.34C9.01 3.81 9.33 3.8 12 3.8Zm0 3.05a5.15 5.15 0 1 0 0 10.3 5.15 5.15 0 0 0 0-10.3Zm0 8.5a3.35 3.35 0 1 1 0-6.7 3.35 3.35 0 0 1 0 6.7Zm6.56-8.7a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0Z"/>
                            </svg>
                        </a>
                    @endif
                    @if ($socialLinks['YouTube'])
                        <a href="{{ $socialLinks['YouTube'] }}" target="_blank" rel="noopener" aria-label="Nature Care Products on YouTube" class="text-brand-300 transition hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.5 6.2a3 3 0 0 0-2.11-2.12C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.39.58A3 3 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3 3 0 0 0 2.11 2.12c1.89.58 9.39.58 9.39.58s7.5 0 9.39-.58a3 3 0 0 0 2.11-2.12A31.5 31.5 0 0 0 24 12a31.5 31.5 0 0 0-.5-5.8ZM9.75 15.5v-7l6.5 3.5-6.5 3.5Z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            @endif

            <p>Made in India</p>
        </div>
    </div>
</footer>
