<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <script>document.documentElement.classList.add('js-motion');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3e999a">

    <title>{{ $metaTitle ?? 'Nature Care Products | Floor Cleaner, Dish Wash & Home Care' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Nature Care Products offers safe, nature-inspired home and personal care products at affordable prices. Naturally Clean, Naturally Safe.' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Nature Care Products">
    <meta property="og:title" content="{{ $metaTitle ?? 'Nature Care Products | Floor Cleaner, Dish Wash & Home Care' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Nature-inspired, safe-for-family home & personal care products at affordable prices.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @if (!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/nature-care-mark.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/nature-care-mark.png') }}">

    @stack('schema')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if (config('naturecare.meta_pixel_id'))
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ config('naturecare.meta_pixel_id') }}');
            fbq('track', 'PageView');
        </script>
    @endif
</head>
<body class="flex min-h-screen flex-col bg-sand-50 pb-16 font-sans text-gray-900 antialiased lg:pb-0">
    <a href="#main-content" class="sr-only focus-visible:not-sr-only focus-visible:fixed focus-visible:left-4 focus-visible:top-4 focus-visible:z-50 focus-visible:rounded-lg focus-visible:bg-brand-600 focus-visible:px-4 focus-visible:py-2 focus-visible:text-sm focus-visible:font-semibold focus-visible:text-white">
        Skip to content
    </a>

    <x-site-header />

    <main id="main-content" class="flex-1">
        {{ $slot }}
    </main>

    <x-site-footer />

    <x-whatsapp-float />

    <x-mobile-bottom-bar />

    @livewireScripts
    @stack('scripts')
</body>
</html>
