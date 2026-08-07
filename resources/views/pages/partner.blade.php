<x-layouts.app
    :meta-title="'Become a Partner | Nature Care Products'"
    :meta-description="'Join Nature Care Products as a Super Stockist, Distributor, or Retailer. Explore business opportunities across India.'"
>
    <section class="bg-brand-50">
        <div class="mx-auto max-w-5xl px-4 py-14 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-brand-900 sm:text-4xl">Become a Nature Care Partner</h1>
            <p class="mt-4 text-gray-600">Grow your business with a trusted, fast-moving home &amp; personal care brand. Choose the opportunity that fits you best.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-3">
            @foreach ($partnerTypes as $type)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-brand-800">{{ $type->label() }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $type->description() }}</p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        @if ($type === \App\Enums\PartnerType::SuperStockist)
                            <li>✔ Exclusive regional rights</li>
                            <li>✔ Highest margin structure</li>
                            <li>✔ Direct support from Nature Care team</li>
                        @elseif ($type === \App\Enums\PartnerType::Distributor)
                            <li>✔ District / city-level coverage</li>
                            <li>✔ Attractive volume-based margins</li>
                            <li>✔ Marketing &amp; promotional support</li>
                        @else
                            <li>✔ Low entry investment</li>
                            <li>✔ Fast-moving, high-demand products</li>
                            <li>✔ Flexible order quantities</li>
                        @endif
                    </ul>
                </div>
            @endforeach
        </div>
    </section>

    <section id="enquiry-form" class="bg-gray-50">
        <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-brand-900">Tell Us About Your Business</h2>
                <p class="mt-2 text-gray-600">Fill in the form below and our partnerships team will reach out to you.</p>
            </div>
            <livewire:partner-enquiry-form />
        </div>
    </section>
</x-layouts.app>
