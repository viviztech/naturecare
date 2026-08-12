<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <label for="product-search" class="sr-only">Search products</label>
            <input
                id="product-search"
                type="search"
                name="q"
                autocomplete="off"
                wire:model.live.debounce.400ms="search"
                placeholder="Search products…"
                class="w-full rounded-lg border border-gray-300 pl-10 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" />
                <path stroke-linecap="round" d="m21 21-4.3-4.3" />
            </svg>
        </div>
    </div>

    <div class="mb-8 flex flex-wrap gap-2">
        <button
            type="button"
            wire:click="selectCategory('')"
            class="rounded-full px-4 py-2 text-sm font-medium transition {{ $categorySlug === '' ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100' }}"
        >
            All Products
        </button>
        @foreach ($categories as $category)
            <button
                type="button"
                wire:click="selectCategory('{{ $category->slug }}')"
                class="rounded-full px-4 py-2 text-sm font-medium transition {{ $categorySlug === $category->slug ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100' }}"
            >
                {{ $category->name }}
            </button>
        @endforeach
    </div>

    <div wire:loading.class="opacity-50" class="transition-opacity">
        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-sand-300 p-12 text-center text-gray-500">
                No products found. Try a different search or category.
            </div>
        @else
            <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
