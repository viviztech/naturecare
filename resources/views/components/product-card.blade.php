@props(['product'])

@php
    $media = $product->getMedia(\App\Models\Product::MEDIA_COLLECTION);
    $primary = $media->first();
    $secondary = $media->count() > 1 ? $media->get(1) : null;
@endphp

<a href="{{ route('products.show', $product) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-sand-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
    <div class="product-media aspect-square bg-brand-50">
        <img
            src="{{ $primary?->getUrl('thumb') ?: '/images/product-placeholder.svg' }}"
            alt="{{ $product->name }}"
            loading="lazy"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
        >
        @if ($secondary)
            <img
                src="{{ $secondary->getUrl('thumb') }}"
                alt=""
                aria-hidden="true"
                loading="lazy"
                class="product-media__secondary h-full w-full object-cover"
            >
        @endif
    </div>
    <div class="flex flex-1 flex-col p-4">
        <span class="font-mono text-xs font-medium uppercase tracking-wide text-aqua-600">{{ $product->category->name }}</span>
        <h3 class="mt-1 font-semibold text-gray-900">{{ $product->name }}</h3>
        <p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ $product->short_description }}</p>
        @if ($price = $product->lowestVariantPrice())
            <p class="mt-2 font-mono text-sm font-semibold text-brand-700">From &#8377;{{ number_format($price) }}</p>
        @endif
    </div>
</a>
