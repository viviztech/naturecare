<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingZone>
 */
class ShippingZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Tamil Nadu',
            'pincode_prefix' => '6',
            'shipping_charge' => 4900,
            'cod_available' => true,
            'free_shipping_above' => 99900,
            'is_active' => true,
        ];
    }
}
