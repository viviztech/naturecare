<x-layouts.app meta-title="Order Confirmed | Nature Care Products">
    <div class="mx-auto max-w-2xl px-4 py-14 text-center sm:px-6 lg:px-8">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-brand-600 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-brand-900">Thank you, {{ $order->contact_name }}!</h1>
        <p class="mt-2 text-gray-600">Your order has been placed successfully.</p>

        <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 text-left shadow-sm">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-gray-900">Order {{ $order->order_number }}</span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->order_status->badgeClasses() }}">
                    {{ $order->order_status->label() }}
                </span>
            </div>

            <div class="mt-4 divide-y divide-gray-100 border-t border-gray-100">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between py-3 text-sm">
                        <span>{{ $item->product_name_snapshot }} ({{ $item->size_label_snapshot }}) × {{ $item->qty }}</span>
                        <span class="font-medium">{{ $item->line_total->format() }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 space-y-1 border-t border-gray-100 pt-4 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span>{{ $order->subtotal->format() }}</span>
                </div>
                @if (! $order->discount_total->isZero())
                    <div class="flex justify-between text-brand-700">
                        <span>Discount</span>
                        <span>-{{ $order->discount_total->format() }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-500">
                    <span>Shipping</span>
                    <span>{{ $order->shipping_charge->format() }}</span>
                </div>
                <div class="flex justify-between font-semibold text-gray-900">
                    <span>Total</span>
                    <span>{{ $order->total->format() }}</span>
                </div>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                Payment method: {{ $order->payment_method->label() }}
            </p>
        </div>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('order.track') }}?order_number={{ $order->order_number }}" class="rounded-lg border border-brand-600 px-6 py-3 font-semibold text-brand-700 hover:bg-brand-50">
                Track This Order
            </a>
            <a href="{{ route('products.index') }}" wire:navigate class="rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white hover:bg-brand-700">
                Continue Shopping
            </a>
        </div>
    </div>
</x-layouts.app>
