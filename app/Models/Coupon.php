<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_cart_value',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            // Deliberately not MoneyCast — meaning of `value` depends on
            // `type` (paise for flat, 1-100 for percent).
            'type' => CouponType::class,
            'value' => 'integer',
            'min_cart_value' => 'integer',
            'max_discount_amount' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format value for display, correctly interpreting the type-dependent
     * meaning of the raw `value` column.
     */
    public function formattedValue(): string
    {
        return match ($this->type) {
            CouponType::Percent => "{$this->value}%",
            CouponType::Flat => Money::fromPaise($this->value)->format(),
        };
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Validate against a cart subtotal, returning a human-readable error
     * message when the coupon cannot be applied, or null when it's valid.
     */
    public function validationError(Money $subtotal): ?string
    {
        if (! $this->isCurrentlyActive()) {
            return 'This coupon is no longer valid.';
        }

        if ($this->min_cart_value !== null && $subtotal->paise() < $this->min_cart_value) {
            $min = Money::fromPaise($this->min_cart_value);

            return "Add items worth {$min->format()} or more to use this coupon.";
        }

        return null;
    }

    public function calculateDiscount(Money $subtotal): Money
    {
        $discount = match ($this->type) {
            CouponType::Flat => Money::fromPaise($this->value),
            CouponType::Percent => $subtotal->multiply($this->value / 100),
        };

        if ($this->max_discount_amount !== null) {
            $discount = Money::min($discount, Money::fromPaise($this->max_discount_amount));
        }

        // Never discount more than the subtotal itself.
        if ($discount->greaterThan($subtotal)) {
            $discount = $subtotal;
        }

        return $discount;
    }
}
