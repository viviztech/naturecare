<?php

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use App\Models\Setting;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockAlert extends TableWidget
{
    protected static ?int $sort = 50;

    protected static ?string $heading = 'Low Stock Variants';

    public function table(Table $table): Table
    {
        $threshold = (int) Setting::get('low_stock_threshold', 10);

        return $table
            ->query(fn (): Builder => ProductVariant::query()
                ->with('product')
                ->where('is_active', true)
                ->where('stock_qty', '<=', $threshold)
                ->orderBy('stock_qty'))
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product'),
                TextColumn::make('size_label')
                    ->label('Size'),
                TextColumn::make('sku'),
                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'warning'),
            ])
            ->paginated(false);
    }
}
