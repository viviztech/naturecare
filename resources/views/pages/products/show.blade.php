<x-layouts.app
    :meta-title="$product->metaTitle()"
    :meta-description="$product->metaDescription()"
>
    @push('schema')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'description' => $product->short_description,
                'image' => $product->getFirstMediaUrl(\App\Models\Product::MEDIA_COLLECTION, 'large'),
                'brand' => ['@type' => 'Brand', 'name' => 'Nature Care Products'],
                'offers' => $product->activeVariants->map(fn ($variant) => [
                    '@type' => 'Offer',
                    'name' => $product->name.' - '.$variant->size_label,
                    'price' => $variant->selling_price->rupees(),
                    'priceCurrency' => 'INR',
                    'availability' => $variant->isInStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                ])->values(),
            ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-brand-700">Home</a> /
            <a href="{{ route('products.index') }}" class="hover:text-brand-700">Products</a> /
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-brand-700">{{ $product->category->name }}</a> /
            <span class="text-gray-700">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2">
            {{-- GALLERY --}}
            <div>
                @php $images = $product->getMedia(\App\Models\Product::MEDIA_COLLECTION); @endphp
                <div class="aspect-square overflow-hidden rounded-2xl border border-sand-200 bg-brand-50">
                    <img
                        src="{{ $images->first()?->getUrl('large') ?: $product->getFirstMediaUrl(\App\Models\Product::MEDIA_COLLECTION, 'large') }}"
                        alt="{{ $product->name }}"
                        fetchpriority="high"
                        class="h-full w-full object-contain p-6"
                    >
                </div>
                @if ($images->count() > 1)
                    <div class="mt-4 grid grid-cols-4 gap-3">
                        @foreach ($images as $image)
                            <div class="aspect-square overflow-hidden rounded-lg border border-sand-200 bg-brand-50">
                                <img src="{{ $image->getUrl('thumb') }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-contain p-2">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- DETAILS --}}
            <div>
                <span class="font-mono text-xs font-medium uppercase tracking-wide text-aqua-600">{{ $product->category->name }}</span>
                <h1 class="mt-1 text-3xl font-bold text-brand-900">{{ $product->name }}</h1>
                <p class="mt-3 text-gray-600">{{ $product->short_description }}</p>

                @if ($product->activeVariants->isNotEmpty())
                    <livewire:add-to-cart :product="$product" />
                @endif

                <a
                    href="{{ $product->whatsappEnquiryUrl() }}"
                    target="_blank"
                    rel="noopener"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-brand-600 px-6 py-3.5 font-semibold text-brand-700 transition hover:bg-brand-50 sm:w-auto"
                >
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Z"/>
                    </svg>
                    Enquire on WhatsApp
                </a>

                @if ($product->description)
                    <div data-reveal class="mt-8 border-t border-sand-200 pt-6">
                        <h2 class="font-semibold text-gray-800">Product Description</h2>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $product->description }}</p>
                    </div>
                @endif

                @if ($product->usage_instructions)
                    <div data-reveal class="mt-6 border-t border-sand-200 pt-6">
                        <h2 class="font-semibold text-gray-800">How to Use</h2>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $product->usage_instructions }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RELATED PRODUCTS --}}
        @if ($relatedProducts->isNotEmpty())
            <div class="mt-16">
                <h2 data-reveal class="text-2xl font-bold text-brand-900">Related Products</h2>
                <div class="mt-6 grid grid-cols-2 gap-5 sm:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
