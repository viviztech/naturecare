<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Sizes / Variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('size_label')
                    ->label('Size')
                    ->required()
                    ->placeholder('e.g. 500ml, 1L, 5L'),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('mrp')
                    ->label('MRP (₹)')
                    ->required()
                    ->rule('numeric')
                    ->inputMode('decimal')
                    ->step('any')
                    ->prefix('₹')
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Support\Money ? $state->rupees() : $state)
                    ->dehydrateStateUsing(fn ($state) => \App\Support\Money::fromRupees((float) $state)),
                TextInput::make('selling_price')
                    ->label('Selling Price (₹)')
                    ->required()
                    ->rule('numeric')
                    ->inputMode('decimal')
                    ->step('any')
                    ->prefix('₹')
                    ->helperText('What the customer actually pays. Defaults to MRP if left equal.')
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Support\Money ? $state->rupees() : $state)
                    ->dehydrateStateUsing(fn ($state) => \App\Support\Money::fromRupees((float) $state)),
                TextInput::make('weight_grams')
                    ->label('Weight (grams)')
                    ->numeric()
                    ->nullable(),
                TextInput::make('stock_qty')
                    ->label('Stock Quantity')
                    ->numeric()
                    ->required()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('size_label')
            ->defaultSort('sort_order')
            ->columns([
                TextInputColumn::make('size_label')
                    ->label('Size'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('mrp')
                    ->label('MRP')
                    ->formatStateUsing(fn ($state) => $state?->format()),
                TextColumn::make('selling_price')
                    ->label('Selling Price')
                    ->formatStateUsing(fn ($state) => $state?->format()),
                TextInputColumn::make('stock_qty')
                    ->label('Stock')
                    ->type('number'),
                IconColumn::make('lowStock')
                    ->label('Low')
                    ->boolean()
                    ->state(fn (Model $record) => $record->stock_qty <= (int) \App\Models\Setting::get('low_stock_threshold', 10))
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('')
                    ->trueColor('warning'),
                ToggleColumn::make('is_active'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
