@php
    $whatsapp = \App\Models\Setting::get('site_whatsapp', config('naturecare.whatsapp_number'));
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp

<nav
    class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-4 border-t border-sand-200 bg-white/95 backdrop-blur lg:hidden"
    style="padding-bottom: env(safe-area-inset-bottom);"
>
    <a href="{{ route('home') }}" wire:navigate class="flex flex-col items-center gap-0.5 py-2.5 text-xs font-medium {{ request()->routeIs('home') ? 'text-brand-700' : 'text-gray-500' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5 12 4l9 7.5M5 10v10h5v-6h4v6h5V10" />
        </svg>
        Home
    </a>

    <a href="{{ route('products.index') }}" wire:navigate class="flex flex-col items-center gap-0.5 py-2.5 text-xs font-medium {{ request()->routeIs('products.*') ? 'text-brand-700' : 'text-gray-500' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" />
        </svg>
        Products
    </a>

    <a href="{{ route('cart.index') }}" wire:navigate aria-label="Cart ({{ $cartCount }} item{{ $cartCount === 1 ? '' : 's' }})" class="relative flex flex-col items-center gap-0.5 py-2.5 text-xs font-medium {{ request()->routeIs('cart.*') ? 'text-brand-700' : 'text-gray-500' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
        </svg>
        Cart
        @if ($cartCount > 0)
            <span class="absolute right-4 top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-brand-600 px-1 font-mono text-[10px] font-semibold text-white">{{ $cartCount }}</span>
        @endif
    </a>

    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-0.5 py-2.5 text-xs font-medium text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Z"/>
        </svg>
        WhatsApp
    </a>
</nav>
