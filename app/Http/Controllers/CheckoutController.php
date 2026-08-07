<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('pages.checkout');
    }
}
