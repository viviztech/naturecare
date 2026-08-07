<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'coupon_id',
        'coupon_code',
        'subtotal',
        'discount_total',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => MoneyCast::class,
            'discount_total' => MoneyCast::class,
            'total' => MoneyCast::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): ?Coupon
    {
        if (! $this->coupon_id) {
            return null;
        }

        return Coupon::query()->find($this->coupon_id);
    }

    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }
}
