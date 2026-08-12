<x-layouts.app
    :meta-title="'Nature Care Products | Floor Cleaner, Dish Wash & Home Care'"
    :meta-description="'Nature Care Products offers safe, nature-inspired home and personal care products at affordable prices. Naturally Clean, Naturally Safe.'"
>
    @push('schema')
        <script type="application/ld+json">
            {!! json_encode([
                '@@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'Nature Care Products',
                'url' => 'https://naturecareplus.com',
                'foundingDate' => '2018',
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => '+91-'.substr(preg_replace('/\D/', '', \App\Models\Setting::get('site_whatsapp', config('naturecare.whatsapp_number'))), -10),
                    'contactType' => 'customer service',
                    'areaServed' => 'IN',
                    'availableLanguage' => ['English'],
                ],
            ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-sand-100">
        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24">
            <div>
                <span class="hero-rise inline-flex items-center gap-2 rounded-full bg-brand-100 px-4 py-1.5 font-mono text-xs font-medium uppercase tracking-wide text-brand-700">
                    🌿 Made in India · Since 2018
                </span>
                <h1 class="hero-rise mt-5 text-5xl font-extrabold tracking-tight text-brand-900 sm:text-6xl lg:text-7xl">
                    Naturally Clean, <span class="text-brand-600">Naturally Safe.</span>
                </h1>
                <p class="hero-rise mt-5 max-w-xl text-lg text-gray-600">
                    Nature Care Products brings you nature-inspired home &amp; personal care essentials that are safe for your family and gentle on your budget.
                </p>
                <div class="hero-rise mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('products.index') }}" class="rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Explore Products
                    </a>
                    <a href="{{ route('partner.index') }}" class="rounded-lg border-2 border-brand-600 px-6 py-3 font-semibold text-brand-700 transition hover:bg-brand-50">
                        Become a Partner
                    </a>
                </div>
            </div>

            {{-- Real-photo "shelf" — the products themselves, not a stock illustration. --}}
            <div class="mx-auto grid w-full max-w-sm grid-cols-2 gap-4 sm:max-w-md">
                @foreach ($featuredProducts->take(4) as $i => $product)
                    <div class="{{ [0 => '-rotate-3', 1 => 'mt-8 rotate-2', 2 => '-mt-4 rotate-3', 3 => 'mt-6 -rotate-2'][$i] ?? '' }} overflow-hidden rounded-2xl border border-sand-200 bg-white p-2.5 shadow-lg">
                        <img
                            src="{{ $product->getFirstMediaUrl(\App\Models\Product::MEDIA_COLLECTION, 'thumb') ?: '/images/product-placeholder.svg' }}"
                            alt="{{ $product->name }}"
                            class="aspect-square w-full rounded-xl object-cover"
                        >
                    </div>
                @endforeach
            </div>
        </div>

        {{-- The signature wipe: a nature/care gradient sweeps away on load. --}}
        <div class="hero-wipe-panel pointer-events-none absolute inset-0 bg-linear-to-br from-brand-600 to-aqua-400" aria-hidden="true"></div>
    </section>

    {{-- CATEGORY GRID --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div data-reveal class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-brand-900">Shop by Category</h2>
            <p class="mt-2 text-gray-600">Everything you need to keep your home and family fresh.</p>
        </div>
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($categories as $category)
                @php $categoryPhoto = $category->activeProducts->first()?->getFirstMediaUrl(\App\Models\Product::MEDIA_COLLECTION, 'thumb'); @endphp
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group flex flex-col items-center rounded-2xl border border-sand-200 bg-white p-5 text-center shadow-sm transition hover:-translate-y-1 hover:border-brand-400 hover:shadow-md">
                    <span class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-sand-100 ring-1 ring-sand-200 transition group-hover:ring-brand-300">
                        @if ($categoryPhoto)
                            <img src="{{ $categoryPhoto }}" alt="" aria-hidden="true" loading="lazy" class="h-full w-full object-contain p-1.5">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-7 w-7 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6l1 4H8l1-4Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1 13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1L6 7Z"/>
                            </svg>
                        @endif
                    </span>
                    <span class="mt-3 font-mono text-xs font-semibold uppercase tracking-wide text-gray-700 group-hover:text-brand-700">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- WHY CHOOSE US --}}
    <section class="bg-brand-50">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div data-reveal class="mb-10 text-center">
                <h2 class="text-3xl font-bold text-brand-900">Why Choose Nature Care</h2>
            </div>
            <div class="grid gap-8 sm:grid-cols-3">
                @foreach ([
                    ['title' => 'Quality You Can Trust', 'desc' => 'Every product is crafted with quality-tested ingredients for safe, effective cleaning.', 'icon' => 'M5 13l4 4L19 7'],
                    ['title' => 'Affordable Pricing', 'desc' => 'Premium quality home care at prices that fit every household budget.', 'icon' => 'M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8V6m0 10v2'],
                    ['title' => 'Made in India', 'desc' => 'Proudly manufactured in India, supporting local jobs and communities.', 'icon' => 'M12 2 2 7l10 5 10-5-10-5Z'],
                ] as $item)
                    <div class="rounded-2xl bg-white p-6 text-center shadow-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                        </span>
                        <h3 class="mt-4 font-semibold text-brand-800">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FEATURED PRODUCTS --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div data-reveal class="mb-10 flex items-end justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-brand-900">Featured Products</h2>
                    <p class="mt-2 text-gray-600">Customer favourites from across our range.</p>
                </div>
                <a href="{{ route('products.index') }}" class="hidden text-sm font-semibold text-brand-700 hover:underline sm:block">View all &rarr;</a>
            </div>
            <div class="-mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <div data-reveal data-reveal-group="home-featured" class="w-52 flex-shrink-0 snap-start sm:w-auto">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- PARTNER CTA STRIP --}}
    <section data-reveal class="bg-brand-600">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 py-12 text-center sm:px-6 lg:flex-row lg:text-left lg:px-8">
            <div>
                <h2 class="text-2xl font-bold text-white">Partner with Nature Care Products</h2>
                <p class="mt-2 text-brand-100">Join our growing network of Super Stockists, Distributors, and Retailers across India.</p>
            </div>
            <a href="{{ route('partner.index') }}" class="whitespace-nowrap rounded-lg bg-white px-6 py-3 font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50">
                Become a Partner
            </a>
        </div>
    </section>

    {{-- TESTIMONIALS --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div data-reveal class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-brand-900">What Our Customers Say</h2>
        </div>
        <div class="grid gap-6 sm:grid-cols-3">
            @foreach ([
                ['name' => 'Priya S.', 'city' => 'Chennai', 'quote' => 'The floor cleaner smells amazing and the price is so much better than the big brands. My whole family loves it.'],
                ['name' => 'Ramesh K.', 'city' => 'Coimbatore', 'quote' => 'I became a retailer for Nature Care last year. Great margins and the products sell themselves.'],
                ['name' => 'Anitha M.', 'city' => 'Madurai', 'quote' => 'Safe for my kids and pets. The dish wash is gentle on hands but still cuts through grease easily.'],
            ] as $testimonial)
                <div class="rounded-2xl border border-sand-200 bg-white p-6 shadow-sm">
                    <p class="text-gray-600">&ldquo;{{ $testimonial['quote'] }}&rdquo;</p>
                    <p class="mt-4 text-sm font-semibold text-brand-800">{{ $testimonial['name'] }} <span class="font-normal text-gray-400">— {{ $testimonial['city'] }}</span></p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- TRUST BADGES --}}
    <section data-reveal class="border-t border-sand-200 bg-sand-100">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-8 text-center font-mono text-sm font-semibold text-gray-500">
                <span class="flex items-center gap-2">📅 Since 2018</span>
                <span class="flex items-center gap-2">🇮🇳 Made in India</span>
                <span class="flex items-center gap-2">✅ Quality Tested</span>
                <span class="flex items-center gap-2">🏆 ISO Certified Process</span>
                <span class="flex items-center gap-2">🚚 Pan-India Distribution</span>
            </div>
        </div>
    </section>
</x-layouts.app>
