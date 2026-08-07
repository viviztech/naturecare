<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon')
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),
                        Select::make('type')
                            ->options(CouponType::class)
                            ->required()
                            ->live()
                            ->default(CouponType::Flat->value),
                        TextInput::make('value')
                            ->label(fn ($get) => $get('type') === CouponType::Percent->value ? 'Discount Percentage' : 'Discount Amount (₹)')
                            ->numeric()
                            ->required()
                            ->suffix(fn ($get) => $get('type') === CouponType::Percent->value ? '%' : null)
                            ->prefix(fn ($get) => $get('type') === CouponType::Percent->value ? null : '₹')
                            ->maxValue(fn ($get) => $get('type') === CouponType::Percent->value ? 100 : null)
                            ->formatStateUsing(fn ($state, $get) => $get('type') === CouponType::Flat->value && $state !== null ? $state / 100 : $state)
                            ->dehydrateStateUsing(fn ($state, $get) => $get('type') === CouponType::Flat->value ? (int) round(((float) $state) * 100) : (int) $state)
                            ->helperText('Percentage (1-100) for percent-type coupons, or rupee amount for flat-discount coupons.'),
                        TextInput::make('min_cart_value')
                            ->label('Minimum Cart Value (₹)')
                            ->numeric()
                            ->nullable()
                            ->prefix('₹')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null),
                        TextInput::make('max_discount_amount')
                            ->label('Max Discount Cap (₹)')
                            ->numeric()
                            ->nullable()
                            ->prefix('₹')
                            ->helperText('Optional cap on discount amount, mainly useful for percent-type coupons.')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null),
                        TextInput::make('usage_limit')
                            ->label('Usage Limit')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave blank for unlimited uses.'),
                        DateTimePicker::make('starts_at')
                            ->nullable(),
                        DateTimePicker::make('expires_at')
                            ->nullable(),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}
