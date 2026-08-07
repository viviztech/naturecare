<x-layouts.app
    :meta-title="'About Us | Nature Care Products'"
    :meta-description="'Learn about Nature Care Products — our mission to bring safe, nature-inspired home and personal care products to every Indian household.'"
>
    <section class="bg-brand-50">
        <div class="mx-auto max-w-4xl px-4 py-14 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 font-mono text-xs font-medium uppercase tracking-wide text-brand-700">
                Est. 2018
            </span>
            <h1 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">About Nature Care Products</h1>
            <p class="mt-4 text-gray-600">Naturally Clean, Naturally Safe — for every Indian home.</p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="max-w-none space-y-4 text-base leading-relaxed text-gray-700">
            <p>
                Nature Care Products was founded in 2018 with a simple belief: keeping your home clean shouldn't mean
                compromising on safety or your family's health. We create nature-inspired home and personal care products
                that are effective, affordable, and gentle enough for everyday use around children and pets.
            </p>
            <p>
                Every product in our range — from floor cleaners to hand wash — is made in India using quality-tested
                ingredients, and manufactured to consistent standards so you can trust what you bring into your home.
            </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-3">
            @foreach ([
                ['title' => 'Our Mission', 'desc' => 'To make safe, effective home care accessible and affordable for every Indian household.'],
                ['title' => 'Our Promise', 'desc' => 'Quality-tested formulations that are tough on dirt and gentle on your family.'],
                ['title' => 'Our Reach', 'desc' => 'Growing across India through a trusted network of stockists, distributors, and retailers.'],
            ] as $item)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-brand-800">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-brand-600">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 py-12 text-center sm:px-6 lg:flex-row lg:text-left lg:px-8">
            <div>
                <h2 class="text-2xl font-bold text-white">Want to stock Nature Care Products?</h2>
                <p class="mt-2 text-brand-100">We're always looking for passionate partners across India.</p>
            </div>
            <a href="{{ route('partner.index') }}" class="whitespace-nowrap rounded-lg bg-white px-6 py-3 font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50">
                Become a Partner
            </a>
        </div>
    </section>
</x-layouts.app>
