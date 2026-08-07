<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Mail\ContactEnquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Priya Sharma',
            'mobile' => '9876543210',
            'email' => 'priya@example.com',
            'subject' => 'Product question',
            'message' => 'Do you ship to Kerala?',
        ], $overrides);
    }

    public function test_invalid_mobile_fails_validation(): void
    {
        Livewire::test(ContactForm::class)
            ->set($this->validData(['mobile' => '12345']))
            ->call('submit')
            ->assertHasErrors(['mobile']);
    }

    public function test_honeypot_blocks_submission(): void
    {
        Livewire::test(ContactForm::class)
            ->set($this->validData())
            ->set('website', 'spam')
            ->call('submit')
            ->assertHasErrors(['website']);
    }

    public function test_successful_submission_creates_enquiry_and_sends_admin_email(): void
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->set($this->validData())
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('contact_enquiries', [
            'name' => 'Priya Sharma',
            'subject' => 'Product question',
        ]);

        Mail::assertQueued(ContactEnquiryReceived::class);
    }

    public function test_rate_limiting_blocks_more_than_three_submissions_per_hour(): void
    {
        Mail::fake();
        RateLimiter::clear('contact-enquiry:127.0.0.1');

        for ($i = 0; $i < 3; $i++) {
            Livewire::test(ContactForm::class)
                ->set($this->validData(['mobile' => '98765432'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)]))
                ->call('submit')
                ->assertHasNoErrors();
        }

        Livewire::test(ContactForm::class)
            ->set($this->validData())
            ->call('submit')
            ->assertHasErrors(['form']);
    }
}
