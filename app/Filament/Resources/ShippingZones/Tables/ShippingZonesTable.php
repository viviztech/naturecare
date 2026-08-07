<?php

namespace App\Filament\Resources\ShippingZones\Tables;

use App\Filament\Imports\ShippingZoneImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ShippingZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('pincode_prefix')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('pincode_prefix')
                    ->label('Prefix')
                    ->searchable(),
                TextColumn::make('shipping_charge')
                    ->formatStateUsing(fn ($state) => $state?->format()),
                TextColumn::make('free_shipping_above')
                    ->formatStateUsing(fn ($state) => $state?->format())
                    ->placeholder('-'),
                IconColumn::make('cod_available')
                    ->label('COD')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('cod_available'),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ShippingZoneImporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
