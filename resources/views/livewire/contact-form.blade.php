<div>
    @if ($submitted)
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-brand-800">Message sent!</h3>
            <p class="mt-2 text-brand-700">Thanks for reaching out. Our team will get back to you shortly.</p>
        </div>
    @else
        @error('form')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="hidden" aria-hidden="true">
                <label for="contact-website">Leave this field empty</label>
                <input type="text" id="contact-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input type="text" wire:model="name" class="mt-1 w-full rounded-lg border border-gray-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Mobile Number *</label>
                    <input type="tel" wire:model="mobile" maxlength="10" class="mt-1 w-full rounded-lg border border-gray-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email (optional)</label>
                    <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border border-gray-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" wire:model="subject" class="mt-1 w-full rounded-lg border border-gray-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Message *</label>
                <textarea wire:model="message" rows="4" class="mt-1 w-full rounded-lg border border-gray-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60 sm:w-auto"
            >
                <span wire:loading.remove wire:target="submit">Send Message</span>
                <span wire:loading wire:target="submit">Sending...</span>
            </button>
        </form>
    @endif
</div>
