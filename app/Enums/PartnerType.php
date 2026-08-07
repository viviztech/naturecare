<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PartnerType: string implements HasColor, HasLabel
{
    case SuperStockist = 'super_stockist';
    case Distributor = 'distributor';
    case Retailer = 'retailer';

    public function label(): string
    {
        return match ($this) {
            self::SuperStockist => 'Super Stockist',
            self::Distributor => 'Distributor',
            self::Retailer => 'Retailer',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperStockist => 'primary',
            self::Distributor => 'info',
            self::Retailer => 'success',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SuperStockist => 'Own a region and supply to distributors under you. Highest margins, largest investment.',
            self::Distributor => 'Cover a district or city and supply to retailers in your area. Balanced investment and returns.',
            self::Retailer => 'Sell directly to end customers from your shop. Lowest entry investment, quick turnaround.',
        };
    }

    public function requiresGodown(): bool
    {
        return match ($this) {
            self::SuperStockist, self::Distributor => true,
            self::Retailer => false,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
