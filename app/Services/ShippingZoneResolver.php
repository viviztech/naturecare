<?php

namespace App\Services;

use App\Models\ShippingZone;

/**
 * Resolves a pincode to the ShippingZone with the longest matching prefix
 * (e.g. a "600" zone wins over a broader "6" zone for pincode 600001).
 * Done in PHP rather than SQL so it behaves identically on sqlite (tests)
 * and MySQL (prod).
 */
class ShippingZoneResolver
{
    public function resolve(string $pincode): ?ShippingZone
    {
        $pincode = trim($pincode);

        return ShippingZone::query()
            ->active()
            ->get()
            ->filter(fn (ShippingZone $zone) => str_starts_with($pincode, $zone->pincode_prefix))
            ->sortByDesc(fn (ShippingZone $zone) => strlen($zone->pincode_prefix))
            ->first();
    }

    public function isServiceable(string $pincode): bool
    {
        return $this->resolve($pincode) !== null;
    }
}
