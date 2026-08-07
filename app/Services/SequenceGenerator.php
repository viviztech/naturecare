<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

/**
 * Race-safe order/invoice number generation. Callers MUST invoke these
 * inside the same DB::transaction() as the row insert/update that consumes
 * the number — the row-lock only holds for the duration of the surrounding
 * transaction.
 *
 * Caveat: sqlite (used by the test suite) has no real row/gap locking, so
 * the concurrency guarantee here is verified by code review + manual
 * concurrent-checkout testing against MySQL, not by PHPUnit.
 */
class SequenceGenerator
{
    public function nextOrderNumber(?Carbon $at = null): string
    {
        $at ??= now();
        $prefix = 'NC-'.$at->format('Ym').'-';

        $count = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->count();

        $next = $count + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function nextInvoiceNumber(?Carbon $at = null): string
    {
        $at ??= now();
        $prefix = 'NC-INV-'.$at->format('Y').'-';

        $count = Order::query()
            ->where('gst_invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->count();

        $next = $count + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
