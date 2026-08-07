<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection(Product::MEDIA_COLLECTION)
                    ->conversion('thumb')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                IconColumn::make('is_commercial')
                    ->boolean()
                    ->label('Commercial'),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_featured'),
            ])
            ->recordActions([
                // Pre-existing Phase 1 quirk fixed in passing: ProductResource sets
                // $recordRouteKeyName = 'id' (so /admin/products/{id}/edit resolves
                // by numeric id, since Product::getRouteKeyName() is 'slug' for the
                // public storefront), but Filament's default row-action URL still
                // used $record->getRouteKey() (slug) — a mismatch that 404'd every
                // time an admin clicked "Edit" in the table. Explicit ->url() below
                // keeps link generation and route resolution using the same key,
                // which also fixes reachability of the new variants relation manager.
                EditAction::make()
                    ->url(fn (Product $record) => ProductResource::getUrl('edit', ['record' => $record->getKey()])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
