<?php

namespace App\Filament\Imports;

use App\Models\ShippingZone;
use App\Support\Money;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ShippingZoneImporter extends Importer
{
    protected static ?string $model = ShippingZone::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('pincode_prefix')
                ->label('Pincode Prefix')
                ->requiredMapping()
                ->rules(['required', 'max:6']),
            ImportColumn::make('shipping_charge')
                ->label('Shipping Charge (₹)')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('free_shipping_above')
                ->label('Free Shipping Above (₹)')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('cod_available')
                ->boolean()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ShippingZone
    {
        return ShippingZone::query()->firstOrNew([
            'pincode_prefix' => $this->data['pincode_prefix'],
        ]);
    }

    protected function beforeSave(): void
    {
        $this->record->shipping_charge = Money::fromRupees((float) $this->data['shipping_charge']);

        $this->record->free_shipping_above = isset($this->data['free_shipping_above']) && $this->data['free_shipping_above'] !== ''
            ? Money::fromRupees((float) $this->data['free_shipping_above'])
            : null;

        $this->record->cod_available = filter_var($this->data['cod_available'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->record->is_active = filter_var($this->data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your shipping zone import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
