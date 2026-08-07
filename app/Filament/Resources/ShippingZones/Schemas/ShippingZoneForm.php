<?php

namespace App\Filament\Resources\ShippingZones\Schemas;

use App\Support\Money;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shipping Zone')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('e.g. "Tamil Nadu"'),
                        TextInput::make('pincode_prefix')
                            ->label('Pincode Prefix')
                            ->required()
                            ->maxLength(6)
                            ->helperText('e.g. "6" matches all pincodes starting with 6. Longest matching prefix wins.'),
                        TextInput::make('shipping_charge')
                            ->label('Shipping Charge (₹)')
                            ->required()
                            ->rule('numeric')
                            ->inputMode('decimal')
                            ->step('any')
                            ->prefix('₹')
                            ->formatStateUsing(fn ($state) => $state instanceof Money ? $state->rupees() : $state)
                            ->dehydrateStateUsing(fn ($state) => Money::fromRupees((float) $state)),
                        TextInput::make('free_shipping_above')
                            ->label('Free Shipping Above (₹)')
                            ->nullable()
                            ->rule('numeric')
                            ->inputMode('decimal')
                            ->step('any')
                            ->prefix('₹')
                            ->helperText('Leave blank to never offer free shipping in this zone.')
                            ->formatStateUsing(fn ($state) => $state instanceof Money ? $state->rupees() : $state)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? Money::fromRupees((float) $state) : null),
                        Toggle::make('cod_available')
                            ->label('Cash on Delivery Available')
                            ->default(true),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}
