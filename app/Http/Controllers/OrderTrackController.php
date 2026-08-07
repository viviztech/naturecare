<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OrderTrackController extends Controller
{
    public function index(): View
    {
        return view('pages.order-track');
    }
}
