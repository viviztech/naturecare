<?php

namespace App\Livewire;

use App\Enums\PartnerType;
use App\Mail\BusinessEnquiryReceived;
use App\Models\BusinessEnquiry;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PartnerEnquiryForm extends Component
{
    public int $step = 1;

    public ?string $partnerType = null;

    public string $name = '';

    public string $firm_name = '';

    public string $mobile = '';

    public string $email = '';

    public string $state = '';

    public string $district = '';

    public string $city = '';

    public string $investment_range = '';

    public string $years_in_business = '';

    public string $current_business = '';

    public ?bool $has_godown = null;

    public string $message = '';

    /** Honeypot field — real users never fill this in. */
    public string $website = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'partnerType' => ['required', Rule::in(array_column(PartnerType::cases(), 'value'))],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'firm_name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'state' => ['required', Rule::in(config('naturecare.indian_states'))],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'investment_range' => ['required', Rule::in(array_keys(config('naturecare.investment_ranges')))],
            'years_in_business' => ['required', Rule::in(array_keys(config('naturecare.years_in_business_ranges')))],
            'current_business' => ['required', Rule::in(array_keys(config('naturecare.business_types')))],
            'has_godown' => [$this->partnerTypeRequiresGodown() ? 'required' : 'nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
            'website' => ['size:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'has_godown.required' => 'Please let us know if you have godown/storage space available.',
            'website.size' => 'Something went wrong. Please refresh and try again.',
        ];
    }

    public function partnerTypeRequiresGodown(): bool
    {
        $type = PartnerType::tryFrom($this->partnerType ?? '');

        return $type?->requiresGodown() ?? false;
    }

    public function partnerTypes(): array
    {
        return PartnerType::cases();
    }

    public function selectPartnerType(string $type): void
    {
        $this->partnerType = $type;

        if (! $this->partnerTypeRequiresGodown()) {
            $this->has_godown = null;
        }

        $this->step = 2;
    }

    public function backToStep1(): void
    {
        $this->step = 1;
    }

    public function submit(): void
    {
        $this->validate();

        $throttleKey = 'partner-enquiry:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('form', "Too many submissions. Please try again in ".ceil($seconds / 60)." minute(s).");

            return;
        }

        RateLimiter::hit($throttleKey, 3600);

        $enquiry = BusinessEnquiry::query()->create([
            'partner_type' => $this->partnerType,
            'name' => $this->name,
            'firm_name' => $this->firm_name,
            'mobile' => $this->mobile,
            'email' => $this->email ?: null,
            'state' => $this->state,
            'district' => $this->district,
            'city' => $this->city,
            'investment_range' => $this->investment_range,
            'years_in_business' => $this->years_in_business,
            'current_business' => $this->current_business,
            'has_godown' => $this->has_godown,
            'message' => $this->message ?: null,
            'status' => 'new',
            'ip_address' => request()->ip(),
        ]);

        $adminEmail = Setting::get('admin_notification_email', config('naturecare.admin_notification_email'));

        Mail::to($adminEmail)->send(new BusinessEnquiryReceived($enquiry));

        $this->dispatch('naturecare-lead-submitted', partnerType: $this->partnerType);

        $this->submitted = true;

        session()->flash('enquiry_partner_type', $this->partnerType);

        $this->redirect(route('partner.thank-you'), navigate: false);
    }

    public function render()
    {
        return view('livewire.partner-enquiry-form', [
            'investmentRanges' => config('naturecare.investment_ranges'),
            'yearsInBusinessRanges' => config('naturecare.years_in_business_ranges'),
            'businessTypes' => config('naturecare.business_types'),
            'indianStates' => config('naturecare.indian_states'),
            'expectedResponseHours' => config('naturecare.expected_response_hours'),
        ]);
    }
}
