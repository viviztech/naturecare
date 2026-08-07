<?php

namespace Tests\Feature;

use App\Enums\PartnerType;
use App\Livewire\PartnerEnquiryForm;
use App\Mail\BusinessEnquiryReceived;
use App\Models\BusinessEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerEnquiryFormTest extends TestCase
{
    use RefreshDatabase;

    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ramesh Kumar',
            'firm_name' => 'Kumar Traders',
            'mobile' => '9876543210',
            'email' => 'ramesh@example.com',
            'state' => 'Tamil Nadu',
            'district' => 'Chennai',
            'city' => 'Chennai',
            'investment_range' => '1_to_5_lakh',
            'years_in_business' => '1_to_3',
            'current_business' => 'kirana_general_store',
            'message' => 'Interested in becoming a retailer.',
        ], $overrides);
    }

    public function test_selecting_a_partner_type_advances_to_step_two(): void
    {
        Livewire::test(PartnerEnquiryForm::class)
            ->assertSet('step', 1)
            ->call('selectPartnerType', PartnerType::Retailer->value)
            ->assertSet('step', 2)
            ->assertSet('partnerType', PartnerType::Retailer->value);
    }

    public function test_godown_field_is_required_for_distributor_but_not_retailer(): void
    {
        Livewire::test(PartnerEnquiryForm::class)
            ->call('selectPartnerType', PartnerType::Distributor->value)
            ->set($this->validData())
            ->call('submit')
            ->assertHasErrors(['has_godown' => 'required']);

        Mail::fake();

        Livewire::test(PartnerEnquiryForm::class)
            ->call('selectPartnerType', PartnerType::Retailer->value)
            ->set($this->validData())
            ->call('submit')
            ->assertHasNoErrors();
    }

    public function test_invalid_mobile_number_fails_validation(): void
    {
        Livewire::test(PartnerEnquiryForm::class)
            ->call('selectPartnerType', PartnerType::Retailer->value)
            ->set($this->validData(['mobile' => '12345']))
            ->call('submit')
            ->assertHasErrors(['mobile']);
    }

    public function test_honeypot_field_blocks_submission(): void
    {
        Livewire::test(PartnerEnquiryForm::class)
            ->call('selectPartnerType', PartnerType::Retailer->value)
            ->set($this->validData())
            ->set('website', 'http://spam.example.com')
            ->call('submit')
            ->assertHasErrors(['website']);
    }

    public function test_successful_submission_creates_enquiry_and_sends_admin_email(): void
    {
        Mail::fake();

        Livewire::test(PartnerEnquiryForm::class)
            ->call('selectPartnerType', PartnerType::Retailer->value)
            ->set($this->validData())
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('business_enquiries', [
            'name' => 'Ramesh Kumar',
            'firm_name' => 'Kumar Traders',
            'partner_type' => PartnerType::Retailer->value,
            'status' => 'new',
        ]);

        Mail::assertQueued(BusinessEnquiryReceived::class, function (BusinessEnquiryReceived $mail) {
            return $mail->enquiry->firm_name === 'Kumar Traders';
        });
    }

    public function test_rate_limiting_blocks_more_than_three_submissions_per_hour(): void
    {
        Mail::fake();
        RateLimiter::clear('partner-enquiry:127.0.0.1');

        for ($i = 0; $i < 3; $i++) {
            Livewire::test(PartnerEnquiryForm::class)
                ->call('selectPartnerType', PartnerType::Retailer->value)
                ->set($this->validData(['mobile' => '98765432'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)]))
                ->call('submit')
                ->assertHasNoErrors();
        }

        Livewire::test(PartnerEnquiryForm::class)
            ->call('selectPartnerType', PartnerType::Retailer->value)
            ->set($this->validData(['mobile' => '9876543299']))
            ->call('submit')
            ->assertHasErrors(['form']);

        $this->assertSame(3, BusinessEnquiry::query()->count());
    }
}
