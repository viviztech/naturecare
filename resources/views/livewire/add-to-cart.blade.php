<div
    class="mt-6 rounded-2xl border border-sand-200 p-5"
    x-data="{ bumped: false }"
    x-on:cart-updated.window="bumped = true; setTimeout(() => bumped = false, 500)"
>
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Available Sizes</h2>

    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($variants as $variant)
            <button
                type="button"
                wire:click="selectVariant({{ $variant->id }})"
                @disabled(! $variant->isInStock())
                class="rounded-xl border-2 p-3 text-center transition disabled:cursor-not-allowed disabled:opacity-40 {{ $variantId === $variant->id ? 'border-brand-600 ring-2 ring-brand-200' : 'border-sand-200 hover:border-brand-400' }}"
            >
                <p class="font-mono font-semibold text-gray-800">{{ $variant->size_label }}</p>
                <p class="font-mono text-sm text-brand-700">{{ $variant->selling_price->format() }}</p>
                @if ($variant->mrp->paise() > $variant->selling_price->paise())
                    <p class="font-mono text-xs text-gray-400 line-through">{{ $variant->mrp->format() }}</p>
                @endif
                @unless ($variant->isInStock())
                    <p class="mt-1 text-xs font-medium text-red-500">Out of stock</p>
                @endunless
            </button>
        @endforeach
    </div>

    @error('variant')
        <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div class="mt-5 flex items-center gap-4">
        <div class="flex items-center rounded-lg border border-gray-300">
            <button type="button" wire:click="decrementQty" aria-label="Decrease quantity" class="px-3 py-2 text-gray-600 hover:bg-gray-50">−</button>
            <span class="w-10 text-center font-medium">{{ $qty }}</span>
            <button type="button" wire:click="incrementQty" aria-label="Increase quantity" class="px-3 py-2 text-gray-600 hover:bg-gray-50">+</button>
        </div>

        <button
            type="button"
            wire:click="addToCart"
            wire:loading.attr="disabled"
            :class="{ 'animate-pop': bumped }"
            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="addToCart">{{ $justAdded ? 'Added ✓ Add More' : 'Add to Cart' }}</span>
            <span wire:loading wire:target="addToCart">Adding…</span>
        </button>
    </div>

    @if ($justAdded)
        <p class="mt-3 text-sm font-medium text-brand-700">
            Added to cart. <a href="{{ route('cart.index') }}" class="underline hover:text-brand-800">View Cart</a>
        </p>
    @endif
</div>
