<?php

namespace App\Livewire;

use App\Mail\ContactEnquiryReceived;
use App\Models\ContactEnquiry;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $mobile = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot field — real users never fill this in. */
    public string $website = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:5', 'max:1000'],
            'website' => ['size:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'website.size' => 'Something went wrong. Please refresh and try again.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $throttleKey = 'contact-enquiry:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('form', 'Too many submissions. Please try again in '.ceil($seconds / 60).' minute(s).');

            return;
        }

        RateLimiter::hit($throttleKey, 3600);

        $enquiry = ContactEnquiry::query()->create([
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email ?: null,
            'subject' => $this->subject ?: null,
            'message' => $this->message,
            'ip_address' => request()->ip(),
        ]);

        $adminEmail = Setting::get('admin_notification_email', config('naturecare.admin_notification_email'));

        Mail::to($adminEmail)->send(new ContactEnquiryReceived($enquiry));

        $this->reset(['name', 'mobile', 'email', 'subject', 'message']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
