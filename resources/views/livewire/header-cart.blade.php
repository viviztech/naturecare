<a
    href="{{ route('cart.index') }}"
    wire:navigate
    x-data="{ bumped: false }"
    x-on:cart-updated.window="bumped = true; setTimeout(() => bumped = false, 500)"
    class="relative flex items-center rounded-lg p-2 text-gray-600 hover:bg-brand-50 focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
    aria-label="View cart ({{ $count }} item{{ $count === 1 ? '' : 's' }})"
>
    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1" />
        <circle cx="20" cy="21" r="1" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
    </svg>
    @if ($count > 0)
        <span :class="{ 'animate-pop': bumped }" class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-brand-600 px-1 font-mono text-xs font-semibold text-white">
            {{ $count }}
        </span>
    @endif
</a>
