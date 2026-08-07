<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Response;

/**
 * Plain (non-Livewire) controller so the GST invoice PDF triggers a real
 * browser download via Content-Disposition — Filament table/page actions
 * run over Livewire's AJAX protocol and can't reliably hand the browser a
 * binary file response, so this is linked to via Action::make(...)->url().
 */
class OrderInvoiceController extends Controller
{
    public function __invoke(Order $order, InvoiceService $invoiceService): Response
    {
        return $invoiceService->download($order);
    }
}
