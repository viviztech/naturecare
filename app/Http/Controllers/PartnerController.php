<?php

namespace App\Http\Controllers;

use App\Enums\PartnerType;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        return view('pages.partner', [
            'partnerTypes' => PartnerType::cases(),
        ]);
    }

    public function thankYou(): View
    {
        return view('pages.partner-thank-you', [
            'partnerType' => PartnerType::tryFrom(session('enquiry_partner_type', '')),
        ]);
    }
}
