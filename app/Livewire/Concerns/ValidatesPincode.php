<?php

namespace App\Livewire\Concerns;

use App\Models\ShippingZone;
use App\Services\ShippingZoneResolver;

trait ValidatesPincode
{
    /**
     * @return array{zone: ?ShippingZone, error: ?string}
     */
    protected function resolvePincode(string $pincode): array
    {
        $pincode = trim($pincode);

        if (! preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            return ['zone' => null, 'error' => 'Please enter a valid 6-digit pincode.'];
        }

        $zone = app(ShippingZoneResolver::class)->resolve($pincode);

        if (! $zone) {
            return ['zone' => null, 'error' => "Sorry, we don't currently deliver to this pincode."];
        }

        return ['zone' => $zone, 'error' => null];
    }
}
