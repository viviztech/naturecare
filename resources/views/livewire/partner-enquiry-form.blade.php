<div id="partner-enquiry-form" class="mx-auto max-w-3xl">
    @if ($submitted)
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-brand-800">Thank you for your interest!</h3>
            <p class="mt-2 text-brand-700">
                Our partnerships team will review your enquiry and get in touch within {{ $expectedResponseHours }} hours.
            </p>
        </div>
    @else
        <div class="mb-6 flex items-center justify-center gap-2 text-sm font-medium">
            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $step === 1 ? 'bg-brand-600 text-white' : 'bg-brand-100 text-brand-700' }}">1</span>
            <span class="{{ $step === 1 ? 'text-brand-800' : 'text-gray-400' }}">Partner Type</span>
            <span class="mx-2 h-px w-8 bg-gray-300"></span>
            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $step === 2 ? 'bg-brand-600 text-white' : 'bg-brand-100 text-brand-700' }}">2</span>
            <span class="{{ $step === 2 ? 'text-brand-800' : 'text-gray-400' }}">Your Details</span>
        </div>

        @error('form')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        {{-- STEP 1: Partner type selection --}}
        @if ($step === 1)
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($this->partnerTypes() as $type)
                    <button
                        type="button"
                        wire:click="selectPartnerType('{{ $type->value }}')"
                        class="flex flex-col items-start rounded-2xl border-2 border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-500 hover:shadow-md {{ $partnerType === $type->value ? 'border-brand-600 ring-2 ring-brand-200' : '' }}"
                    >
                        <span class="text-lg font-semibold text-brand-800">{{ $type->label() }}</span>
                        <span class="mt-2 text-sm text-gray-600">{{ $type->description() }}</span>
                    </button>
                @endforeach
            </div>
            @error('partnerType')
                <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
            @enderror
        @endif

        {{-- STEP 2: Details form --}}
        @if ($step === 2)
            <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center justify-between rounded-lg bg-brand-50 px-4 py-3">
                    <span class="text-sm font-medium text-brand-800">
                        Applying as: <strong>{{ \App\Enums\PartnerType::from($partnerType)->label() }}</strong>
                    </span>
                    <button type="button" wire:click="backToStep1" class="text-sm font-medium text-aqua-600 hover:underline">Change</button>
                </div>

                {{-- Honeypot field — hidden from real users --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Leave this field empty</label>
                    <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" wire:model="name" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Firm / Shop Name *</label>
                        <input type="text" wire:model="firm_name" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        @error('firm_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mobile Number (WhatsApp) *</label>
                        <input type="tel" wire:model="mobile" maxlength="10" placeholder="10-digit mobile number" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        @error('mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email (optional)</label>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">State *</label>
                        <select wire:model="state" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select State</option>
                            @foreach ($indianStates as $stateOption)
                                <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                            @endforeach
                        </select>
                        @error('state') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">District *</label>
                        <input type="text" wire:model="district" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        @error('district') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">City / Town *</label>
                        <input type="text" wire:model="city" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Investment Capacity *</label>
                        <select wire:model="investment_range" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select Range</option>
                            @foreach ($investmentRanges as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('investment_range') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Years in Current Business *</label>
                        <select wire:model="years_in_business" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select</option>
                            @foreach ($yearsInBusinessRanges as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('years_in_business') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Business Type *</label>
                        <select wire:model="current_business" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select</option>
                            @foreach ($businessTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('current_business') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if ($this->partnerTypeRequiresGodown())
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Godown / Storage Space Available? *</label>
                            <div class="mt-2 flex gap-4">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" wire:model="has_godown" value="1" class="text-brand-600 focus:ring-brand-500">
                                    Yes
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" wire:model="has_godown" value="0" class="text-brand-600 focus:ring-brand-500">
                                    No
                                </label>
                            </div>
                            @error('has_godown') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Message (optional)</label>
                    <textarea wire:model="message" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500"></textarea>
                    @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-brand-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="submit">Submit Enquiry</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
            </form>
        @endif
    @endif
</div>
