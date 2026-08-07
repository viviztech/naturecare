<div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-brand-900">Track Your Order</h1>
    <p class="mt-2 text-gray-600">Enter your mobile number and order number to check the status of your order.</p>

    <form wire:submit="track" class="mt-6 space-y-4 rounded-2xl border border-sand-200 bg-white p-6 shadow-sm">
        @error('form')
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div>
            <label class="block text-sm font-medium text-gray-700">Mobile Number *</label>
            <input type="tel" wire:model="mobile" maxlength="10" wire:loading.attr="disabled" wire:target="track" class="mt-1 w-full rounded-lg border-gray-300 font-mono focus:border-brand-500 focus:ring-brand-500">
            @error('mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Order Number *</label>
            <input type="text" wire:model="orderNumber" placeholder="NC-202608-0001" wire:loading.attr="disabled" wire:target="track" class="mt-1 w-full rounded-lg border-gray-300 font-mono focus:border-brand-500 focus:ring-brand-500">
            @error('orderNumber') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="track" class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60">
            <span wire:loading.remove wire:target="track">Track Order</span>
            <span wire:loading wire:target="track" class="inline-flex items-center gap-2"><x-ui.spinner class="h-4 w-4" /> Searching…</span>
        </button>
    </form>

    @if ($order)
        <div class="mt-8 rounded-2xl border border-sand-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Order <span class="font-mono">{{ $order->order_number }}</span></h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->order_status->badgeClasses() }}">
                    {{ $order->order_status->label() }}
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-500">Placed on {{ $order->created_at->format('d M Y') }}</p>

            @if ($order->tracking_number)
                <p class="mt-2 text-sm text-gray-700">
                    Tracking Number: <strong class="font-mono">{{ $order->tracking_number }}</strong>
                    @if ($order->tracking_url)
                        · <a href="{{ $order->tracking_url }}" target="_blank" class="text-aqua-600 hover:underline">Track Shipment</a>
                    @endif
                </p>
            @endif

            <div class="mt-4 divide-y divide-sand-200 border-t border-sand-200">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between py-3 text-sm">
                        <span>{{ $item->product_name_snapshot }} ({{ $item->size_label_snapshot }}) × {{ $item->qty }}</span>
                        <span class="font-mono font-medium">{{ $item->line_total->format() }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex justify-between border-t border-sand-200 pt-4 font-semibold text-gray-900">
                <span>Total</span>
                <span class="font-mono">{{ $order->total->format() }}</span>
            </div>
        </div>
    @endif
</div>
