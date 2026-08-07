<?php

namespace App\Filament\Widgets;

use App\Models\BusinessEnquiry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentEnquiriesTable extends TableWidget
{
    protected static ?int $sort = 70;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Business Enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => BusinessEnquiry::query()->latest()->limit(10))
            ->columns([
                TextColumn::make('partner_type')
                    ->badge(),
                TextColumn::make('name'),
                TextColumn::make('firm_name'),
                TextColumn::make('city'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->since(),
            ])
            ->paginated(false);
    }
}
