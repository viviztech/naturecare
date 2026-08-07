<div
    x-data="{ paying: false }"
    x-on:razorpay-checkout-open.window="
        const detail = $event.detail;
        const rzp = new Razorpay({
            key: detail.key,
            amount: detail.amount,
            currency: 'INR',
            name: detail.name,
            order_id: detail.orderId,
            prefill: {
                name: detail.prefillName,
                email: detail.prefillEmail,
                contact: detail.prefillContact,
            },
            theme: { color: '#3e999a' },
            handler: function (response) {
                $wire.confirmRazorpayPayment(
                    response.razorpay_order_id,
                    response.razorpay_payment_id,
                    response.razorpay_signature
                );
            },
            modal: {
                ondismiss: function () {
                    paying = false;
                    $wire.razorpayCancelled();
                },
            },
        });
        rzp.on('payment.failed', function (response) {
            paying = false;
            $wire.razorpayFailed(response.error.description ?? 'Payment failed.');
        });
        paying = true;
        rzp.open();
    "
    x-on:razorpay-payment-verification-failed.window="paying = false"
    class="mx-auto max-w-3xl px-4 pb-24 pt-10 sm:px-6 lg:px-8 lg:pb-10"
>
    <h1 class="text-2xl font-bold text-brand-900">Checkout</h1>

    <div class="mb-8 mt-6 flex items-center justify-center gap-2 text-sm font-medium" role="group" aria-label="Checkout progress">
        @foreach ([1 => 'Contact', 2 => 'Address', 3 => 'Payment'] as $n => $label)
            @if ($n > 1)
                <span class="mx-2 h-px w-8 {{ $step > $n - 1 ? 'bg-brand-500' : 'bg-sand-300' }}" aria-hidden="true"></span>
            @endif
            <div class="flex items-center gap-2" @if ($step === $n) aria-current="step" @endif>
                @if ($step > $n)
                    <span class="animate-pop flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                @else
                    <span class="flex h-7 w-7 items-center justify-center rounded-full font-mono {{ $step === $n ? 'bg-brand-600 text-white ring-2 ring-brand-200 ring-offset-2' : 'bg-brand-100 text-brand-700' }}">{{ $n }}</span>
                @endif
                <span class="{{ $step >= $n ? 'text-brand-800' : 'text-gray-400' }}">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    <div aria-live="polite">
        @error('form')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        @if (! empty($cartIssues))
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                <p class="font-semibold">Please review your cart:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($cartIssues as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('cart.index') }}" wire:navigate class="mt-2 inline-block underline">Go to cart</a>
            </div>
        @endif
    </div>

    {{-- STEP 1: Contact --}}
    @if ($step === 1)
        <form wire:submit="goToAddress" class="space-y-5 rounded-2xl border border-sand-200 bg-white p-6 shadow-sm sm:p-8">
            <div>
                <label for="checkout-name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                <input id="checkout-name" type="text" autocomplete="name" wire:model="name" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="checkout-mobile" class="block text-sm font-medium text-gray-700">Mobile Number *</label>
                <input id="checkout-mobile" type="tel" inputmode="numeric" autocomplete="tel" wire:model="mobile" maxlength="10" placeholder="10-digit mobile number…" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                @error('mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="checkout-email" class="block text-sm font-medium text-gray-700">Email (optional)</label>
                <input id="checkout-email" type="email" autocomplete="email" spellcheck="false" wire:model="email" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="goToAddress" class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="goToAddress">Continue to Address</span>
                <span wire:loading wire:target="goToAddress" class="inline-flex items-center gap-2"><x-ui.spinner class="h-4 w-4" /> Continuing…</span>
            </button>
        </form>
    @endif

    {{-- STEP 2: Address --}}
    @if ($step === 2)
        <form wire:submit="goToPayment" class="space-y-5 rounded-2xl border border-sand-200 bg-white p-6 shadow-sm sm:p-8">
            <div>
                <label for="checkout-line1" class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                <input id="checkout-line1" type="text" autocomplete="address-line1" wire:model="line1" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                @error('line1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="checkout-line2" class="block text-sm font-medium text-gray-700">Address Line 2</label>
                <input id="checkout-line2" type="text" autocomplete="address-line2" wire:model="line2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="checkout-city" class="block text-sm font-medium text-gray-700">City / Town *</label>
                    <input id="checkout-city" type="text" autocomplete="address-level2" wire:model="city" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                    @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="checkout-pincode" class="block text-sm font-medium text-gray-700">Pincode *</label>
                    <input id="checkout-pincode" type="text" inputmode="numeric" autocomplete="postal-code" wire:model="pincode" maxlength="6" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                    @error('pincode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="backToContact" class="rounded-lg border border-sand-300 px-6 py-3 font-semibold text-gray-700 hover:bg-sand-100">Back</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="goToPayment" class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="goToPayment">Continue to Payment</span>
                    <span wire:loading wire:target="goToPayment" class="inline-flex items-center gap-2"><x-ui.spinner class="h-4 w-4" /> Continuing…</span>
                </button>
            </div>
        </form>
    @endif

    {{-- STEP 3: Payment --}}
    @if ($step === 3)
        <div class="space-y-5 rounded-2xl border border-sand-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="rounded-lg bg-brand-50 p-4 text-sm text-brand-800">
                Delivering to {{ $line1 }}, {{ $city }} - {{ $pincode }}
                @if ($shippingChargePaise !== null)
                    <br>Shipping: <span class="font-mono">{{ \App\Support\Money::fromPaise($shippingChargePaise)->format() }}</span>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Payment Method *</label>
                <div class="mt-2 space-y-2">
                    @if ($codAvailable)
                        <label class="flex items-center gap-3 rounded-lg border border-sand-200 p-3 has-checked:border-brand-500 has-checked:bg-brand-50">
                            <input type="radio" wire:model="paymentMethod" value="cod" class="text-brand-600 focus:ring-brand-500">
                            <span>Cash on Delivery</span>
                        </label>
                    @endif
                    <label class="flex items-center gap-3 rounded-lg border border-sand-200 p-3 has-checked:border-brand-500 has-checked:bg-brand-50">
                        <input type="radio" wire:model="paymentMethod" value="razorpay" class="text-brand-600 focus:ring-brand-500">
                        <span>Pay Online (Cards / UPI / Netbanking)</span>
                    </label>
                </div>
                @error('paymentMethod') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="button" wire:click="backToAddress" :disabled="paying" class="rounded-lg border border-sand-300 px-6 py-3 font-semibold text-gray-700 hover:bg-sand-100 disabled:opacity-50">Back</button>
                <button
                    type="button"
                    wire:click="placeOrder"
                    wire:loading.attr="disabled"
                    wire:target="placeOrder"
                    :disabled="paying"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60"
                >
                    <template x-if="!paying">
                        <span wire:loading.remove wire:target="placeOrder">Place Order</span>
                    </template>
                    <span wire:loading wire:target="placeOrder">Processing…</span>
                    <span x-show="paying" x-cloak class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span> Complete payment in the popup…
                    </span>
                </button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush
