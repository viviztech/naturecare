<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasLabel
{
    case Percent = 'percent';
    case Flat = 'flat';

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Percentage Discount',
            self::Flat => 'Flat Discount',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
