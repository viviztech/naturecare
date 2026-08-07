<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\Money;

/**
 * GST split calculation. Stored line/order totals are tax-inclusive
 * (matches the existing MRP convention) — this service backs out the
 * taxable value at a configurable rate rather than adding tax on top.
 */
class GstService
{
    public function ratePercent(): float
    {
        return (float) Setting::get('gst_rate_percent', 18);
    }

    /**
     * @return array{cgst: Money, sgst: Money, igst: Money, taxable_value: Money}
     */
    public function calculate(Money $taxInclusiveAmount, ?string $shippingState): array
    {
        $rate = $this->ratePercent();

        $totalTaxPaise = (int) round($taxInclusiveAmount->paise() * $rate / (100 + $rate));
        $taxableValuePaise = $taxInclusiveAmount->paise() - $totalTaxPaise;

        $isIntraStateTamilNadu = $shippingState !== null && strcasecmp(trim($shippingState), 'Tamil Nadu') === 0;

        if (! $isIntraStateTamilNadu) {
            return [
                'cgst' => Money::zero(),
                'sgst' => Money::zero(),
                'igst' => Money::fromPaise($totalTaxPaise),
                'taxable_value' => Money::fromPaise($taxableValuePaise),
            ];
        }

        $cgstPaise = intdiv($totalTaxPaise, 2);
        $sgstPaise = $totalTaxPaise - $cgstPaise; // any 1-paise rounding remainder folds into SGST

        return [
            'cgst' => Money::fromPaise($cgstPaise),
            'sgst' => Money::fromPaise($sgstPaise),
            'igst' => Money::zero(),
            'taxable_value' => Money::fromPaise($taxableValuePaise),
        ];
    }

    /**
     * Convenience wrapper matching the plan's naming — computes GST on the
     * order's taxable amount (subtotal minus discount) based on its
     * shipping state.
     *
     * @return array{cgst: Money, sgst: Money, igst: Money, taxable_value: Money}
     */
    public function calculateForOrder(Money $taxableAmount, ?string $shippingState): array
    {
        return $this->calculate($taxableAmount, $shippingState);
    }
}
