<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->schema([
                    Select::make('order_status')
                        ->label('Order Status')
                        ->options(OrderStatus::class)
                        ->default(fn ($record) => $record->order_status->value)
                        ->required(),
                ])
                ->action(function (array $data, OrderService $orderService) {
                    // Filament's Select::options(OrderStatus::class) already casts the
                    // submitted value back into an OrderStatus instance automatically —
                    // calling ::from() again on an already-enum value would throw.
                    $status = $data['order_status'] instanceof OrderStatus
                        ? $data['order_status']
                        : OrderStatus::from($data['order_status']);

                    $orderService->updateStatus($this->getRecord(), $status);

                    Notification::make()->title('Order status updated')->success()->send();
                }),

            Action::make('addTracking')
                ->label('Add Tracking')
                ->icon('heroicon-o-truck')
                ->color('gray')
                ->schema([
                    TextInput::make('tracking_number')
                        ->default(fn ($record) => $record->tracking_number)
                        ->required(),
                    TextInput::make('tracking_url')
                        ->label('Tracking URL')
                        ->url()
                        ->default(fn ($record) => $record->tracking_url),
                ])
                ->action(function (array $data, OrderService $orderService) {
                    $orderService->addTracking($this->getRecord(), $data['tracking_number'], $data['tracking_url'] ?: null);

                    Notification::make()->title('Tracking details saved')->success()->send();
                }),

            Action::make('markCodPaid')
                ->label('Mark COD Paid')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn ($record) => $record->payment_method === PaymentMethod::Cod && $record->payment_status !== PaymentStatus::Paid)
                ->requiresConfirmation()
                ->action(function (OrderService $orderService) {
                    $orderService->markCodPaid($this->getRecord());

                    Notification::make()->title('Order marked as paid')->success()->send();
                }),

            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->isCancellable())
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('reason')
                        ->label('Cancellation Reason')
                        ->required(),
                ])
                ->action(function (array $data, OrderService $orderService) {
                    $orderService->cancel($this->getRecord(), $data['reason']);

                    Notification::make()->title('Order cancelled')->success()->send();
                }),

            Action::make('downloadInvoice')
                ->label('Download GST Invoice')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn ($record) => route('admin.orders.invoice', $record))
                ->openUrlInNewTab(),
        ];
    }
}
