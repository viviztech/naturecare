<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case PendingPayment = 'pending_payment';
    case Placed = 'placed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::Placed => 'Placed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingPayment => 'gray',
            self::Placed => 'info',
            self::Processing => 'warning',
            self::Shipped => 'primary',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }

    /**
     * Whether an order in this status can still be cancelled.
     */
    public function isCancellable(): bool
    {
        return in_array($this, [self::Placed, self::Processing], true);
    }

    /**
     * Literal Tailwind utility classes for storefront badges. Deliberately
     * static strings (not dynamically interpolated class name fragments)
     * so Tailwind v4's content scanner can find them.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::PendingPayment => 'bg-gray-100 text-gray-700',
            self::Placed => 'bg-sky-100 text-sky-700',
            self::Processing => 'bg-amber-100 text-amber-700',
            self::Shipped => 'bg-aqua-100 text-aqua-700',
            self::Delivered => 'bg-brand-100 text-brand-700',
            self::Cancelled => 'bg-red-100 text-red-700',
        };
    }
}
