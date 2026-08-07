<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingZoneFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'pincode_prefix',
        'shipping_charge',
        'cod_available',
        'free_shipping_above',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'shipping_charge' => MoneyCast::class,
            'cod_available' => 'boolean',
            'free_shipping_above' => MoneyCast::class,
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function chargeFor(\App\Support\Money $subtotal): \App\Support\Money
    {
        if ($this->free_shipping_above !== null && ! $subtotal->lessThan($this->free_shipping_above)) {
            return \App\Support\Money::zero();
        }

        return $this->shipping_charge;
    }
}
