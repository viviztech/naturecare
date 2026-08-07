<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected SequenceGenerator $sequenceGenerator,
    ) {}

    /**
     * GST invoice numbers are generated lazily on first PDF download and
     * persisted so re-downloads reuse the same number.
     */
    public function ensureInvoiceNumber(Order $order): string
    {
        if ($order->gst_invoice_number) {
            return $order->gst_invoice_number;
        }

        return DB::transaction(function () use ($order) {
            $order->refresh();

            if ($order->gst_invoice_number) {
                return $order->gst_invoice_number;
            }

            $number = $this->sequenceGenerator->nextInvoiceNumber();
            $order->update(['gst_invoice_number' => $number]);

            return $number;
        });
    }

    public function download(Order $order): Response
    {
        $invoiceNumber = $this->ensureInvoiceNumber($order);

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order->load('items'),
            'invoiceNumber' => $invoiceNumber,
            'gstin' => Setting::get('gstin', ''),
            'sellerAddress' => Setting::get('site_address', ''),
        ]);

        return $pdf->download("invoice-{$invoiceNumber}.pdf");
    }
}
