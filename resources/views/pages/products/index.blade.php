<x-layouts.app
    :meta-title="'Our Products | Nature Care Products'"
    :meta-description="'Browse Nature Care Products range of home care, kitchen care, laundry care, and personal care products.'"
>
    <section class="bg-brand-50">
        <div data-reveal class="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-brand-900 sm:text-4xl">Our Products</h1>
            <p class="mt-3 text-gray-600">Nature-inspired, family-safe products for every corner of your home.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <livewire:product-catalog />
    </section>
</x-layouts.app>
