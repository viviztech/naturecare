@php
    $navLinks = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Products', 'route' => 'products.index'],
        ['label' => 'About Us', 'route' => 'about'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
@endphp

<header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-sand-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center">
            <img
                src="{{ asset('images/nature-care-logo.jpeg') }}"
                alt="Nature Care Products"
                width="374"
                height="96"
                class="h-9 w-auto sm:h-10"
            >
        </a>

        <nav class="hidden items-center gap-8 lg:flex">
            @foreach ($navLinks as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="text-sm font-medium {{ request()->routeIs($link['route']) ? 'text-brand-700' : 'text-gray-600 hover:text-brand-700' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            @livewire('header-cart')
            <a href="{{ route('partner.index') }}" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                Become a Partner
            </a>
        </div>

        <div class="flex items-center gap-1 lg:hidden">
            @livewire('header-cart')

            <button
                @click="mobileOpen = !mobileOpen"
                class="rounded-lg p-2 text-gray-600 hover:bg-brand-50 focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                aria-label="Toggle menu"
                :aria-expanded="mobileOpen.toString()"
                aria-controls="mobile-nav-panel"
            >
                <svg x-show="!mobileOpen" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    <div id="mobile-nav-panel" x-show="mobileOpen" x-collapse x-cloak class="border-t border-sand-200 lg:hidden">
        <nav class="flex flex-col gap-1 px-4 py-3">
            @foreach ($navLinks as $link)
                <a
                    href="{{ route($link['route']) }}"
                    @click="mobileOpen = false"
                    class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($link['route']) ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-brand-50' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('partner.index') }}" class="mt-2 rounded-lg bg-brand-600 px-3 py-2.5 text-center text-sm font-semibold text-white">
                Become a Partner
            </a>
        </nav>
    </div>
</header>
