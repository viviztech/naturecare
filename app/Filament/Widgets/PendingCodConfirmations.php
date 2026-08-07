<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingCodConfirmations extends TableWidget
{
    protected static ?int $sort = 60;

    protected static ?string $heading = 'Pending COD Confirmations';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::query()
                ->where('payment_method', PaymentMethod::Cod->value)
                ->where('payment_status', PaymentStatus::Pending->value)
                ->where('order_status', '!=', OrderStatus::Cancelled->value)
                ->latest())
            ->columns([
                TextColumn::make('order_number'),
                TextColumn::make('contact_name')
                    ->label('Customer'),
                TextColumn::make('contact_mobile')
                    ->label('Mobile'),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => $state?->format()),
                TextColumn::make('order_status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->since(),
            ])
            ->recordUrl(fn (Order $record) => route('filament.admin.resources.orders.view', $record))
            ->paginated(false);
    }
}
