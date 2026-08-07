<div class="mx-auto max-w-5xl px-4 pb-24 pt-10 sm:px-6 lg:px-8 lg:pb-10">
    <h1 class="text-2xl font-bold text-brand-900">Your Cart</h1>

    @if ($items->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-sand-300 p-12 text-center text-gray-500">
            <p>Your cart is empty.</p>
            <a href="{{ route('products.index') }}" wire:navigate class="mt-4 inline-block rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                Browse Products
            </a>
        </div>
    @else
        <div class="mt-8 grid gap-8 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($items as $item)
                    @php
                        $decTarget = "updateQty({$item->id}, ".($item->qty - 1).")";
                        $incTarget = "updateQty({$item->id}, ".($item->qty + 1).")";
                        $removeTarget = "removeItem({$item->id})";
                    @endphp
                    <div wire:key="cart-item-{{ $item->id }}" class="flex items-center gap-4 rounded-2xl border border-sand-200 bg-white p-4">
                        <img
                            src="{{ $item->variant->product->getFirstMediaUrl(\App\Models\Product::MEDIA_COLLECTION, 'thumb') ?: '/images/product-placeholder.svg' }}"
                            alt="{{ $item->variant->product->name }}"
                            class="h-16 w-16 rounded-lg object-cover"
                        >
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">{{ $item->variant->product->name }}</p>
                            <p class="font-mono text-sm text-gray-500">{{ $item->variant->size_label }} · {{ $item->unit_price->format() }} each</p>
                        </div>
                        <div class="flex items-center rounded-lg border border-sand-300">
                            <button type="button" wire:click="{{ $decTarget }}" wire:loading.attr="disabled" wire:target="{{ $decTarget }}" aria-label="Decrease quantity of {{ $item->variant->product->name }}" class="flex h-8 w-8 items-center justify-center text-gray-600 hover:bg-sand-100 disabled:opacity-50">
                                <span wire:loading.remove wire:target="{{ $decTarget }}">−</span>
                                <x-ui.spinner wire:loading wire:target="{{ $decTarget }}" class="h-3.5 w-3.5" />
                            </button>
                            <span class="w-8 text-center font-mono text-sm font-medium">{{ $item->qty }}</span>
                            <button type="button" wire:click="{{ $incTarget }}" wire:loading.attr="disabled" wire:target="{{ $incTarget }}" aria-label="Increase quantity of {{ $item->variant->product->name }}" class="flex h-8 w-8 items-center justify-center text-gray-600 hover:bg-sand-100 disabled:opacity-50">
                                <span wire:loading.remove wire:target="{{ $incTarget }}">+</span>
                                <x-ui.spinner wire:loading wire:target="{{ $incTarget }}" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <p class="w-24 text-right font-mono font-semibold text-brand-700">{{ $item->lineTotal()->format() }}</p>
                        <button type="button" wire:click="{{ $removeTarget }}" wire:loading.attr="disabled" wire:target="{{ $removeTarget }}" class="text-gray-400 hover:text-red-500 disabled:opacity-50" aria-label="Remove {{ $item->variant->product->name }} from cart">
                            <svg wire:loading.remove wire:target="{{ $removeTarget }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                            <x-ui.spinner wire:loading wire:target="{{ $removeTarget }}" class="h-5 w-5" />
                        </button>
                    </div>
                @endforeach

                {{-- Pincode / delivery estimate --}}
                <div class="rounded-2xl border border-sand-200 bg-white p-4">
                    <label for="cart-pincode" class="block text-sm font-medium text-gray-700">Check delivery availability</label>
                    <div class="mt-2 flex gap-2">
                        <input id="cart-pincode" type="text" inputmode="numeric" autocomplete="postal-code" wire:model="pincode" maxlength="6" placeholder="Enter pincode…" wire:loading.attr="disabled" wire:target="checkPincode" class="w-40 rounded-lg border-gray-300 font-mono text-sm focus:border-brand-500 focus:ring-brand-500">
                        <button type="button" wire:click="checkPincode" wire:loading.attr="disabled" wire:target="checkPincode" class="flex items-center gap-1.5 rounded-lg bg-aqua-600 px-4 py-2 text-sm font-semibold text-white hover:bg-aqua-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="checkPincode">Check</span>
                            <span wire:loading wire:target="checkPincode" class="inline-flex items-center gap-1.5"><x-ui.spinner class="h-3.5 w-3.5" /> Checking…</span>
                        </button>
                    </div>
                    <div aria-live="polite">
                        @if ($pincodeError)
                            <p class="mt-2 text-sm text-red-600">{{ $pincodeError }}</p>
                        @elseif ($shippingChargePaise !== null)
                            <p class="mt-2 text-sm text-brand-700">
                                Delivery available. Shipping: {{ \App\Support\Money::fromPaise($shippingChargePaise)->format() }}
                                @if ($codAvailable) · Cash on Delivery available @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-sand-200 bg-white p-5 lg:sticky lg:top-24">
                <h2 class="font-semibold text-gray-900">Order Summary</h2>

                <div class="mt-3 flex gap-2">
                    <label for="coupon-code" class="sr-only">Coupon code</label>
                    <input id="coupon-code" type="text" autocomplete="off" wire:model="couponCode" placeholder="Coupon code" wire:loading.attr="disabled" wire:target="applyCoupon" class="flex-1 rounded-lg border-gray-300 text-sm uppercase focus:border-brand-500 focus:ring-brand-500">
                    @if ($cart?->coupon_code)
                        <button type="button" wire:click="removeCoupon" wire:loading.attr="disabled" wire:target="removeCoupon" class="flex items-center gap-1.5 rounded-lg border border-sand-300 px-3 py-2 text-sm hover:bg-sand-100 disabled:opacity-60">
                            <span wire:loading.remove wire:target="removeCoupon">Remove</span>
                            <x-ui.spinner wire:loading wire:target="removeCoupon" class="h-3.5 w-3.5" />
                        </button>
                    @else
                        <button type="button" wire:click="applyCoupon" wire:loading.attr="disabled" wire:target="applyCoupon" class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="applyCoupon">Apply</span>
                            <x-ui.spinner wire:loading wire:target="applyCoupon" class="h-3.5 w-3.5" />
                        </button>
                    @endif
                </div>
                <div aria-live="polite">
                    @if ($couponMessage)
                        <p class="mt-2 text-sm {{ $couponSuccess ? 'text-brand-700' : 'text-red-600' }}">{{ $couponMessage }}</p>
                    @endif
                </div>

                <div class="mt-4 space-y-2 border-t border-sand-200 pt-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-mono">{{ $cart?->subtotal->format() ?? '₹0.00' }}</span>
                    </div>
                    @if ($cart && ! $cart->discount_total->isZero())
                        <div class="flex justify-between text-brand-700">
                            <span>Discount</span>
                            <span class="font-mono">-{{ $cart->discount_total->format() }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-gray-900">
                        <span>Total (excl. shipping)</span>
                        <span class="font-mono">{{ $cart?->total->format() ?? '₹0.00' }}</span>
                    </div>
                </div>

                <a href="{{ route('checkout.index') }}" wire:navigate class="mt-5 flex w-full items-center justify-center rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    @endif
</div>
