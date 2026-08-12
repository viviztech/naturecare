@php
    $phone = \App\Models\Setting::get('site_phone', '+91 99999 99999');
    $whatsapp = \App\Models\Setting::whatsappNumber();
    $email = \App\Models\Setting::get('site_email', 'enquiry@naturecareplus.com');
    $address = \App\Models\Setting::get('site_address', 'Chennai, Tamil Nadu, India');
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
            <p>Made in India</p>
        </div>
    </div>
</footer>
