<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use App\Support\Money;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        ShippingZone::query()->updateOrCreate(
            ['pincode_prefix' => '6'],
            [
                'name' => 'Tamil Nadu',
                'shipping_charge' => Money::fromRupees(49),
                'cod_available' => true,
                'free_shipping_above' => Money::fromRupees(999),
                'is_active' => true,
            ]
        );
    }
}
