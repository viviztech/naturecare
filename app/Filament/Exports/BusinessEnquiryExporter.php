<?php

namespace App\Filament\Exports;

use App\Models\BusinessEnquiry;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class BusinessEnquiryExporter extends Exporter
{
    protected static ?string $model = BusinessEnquiry::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('partner_type')
                ->formatStateUsing(fn ($state) => $state?->label()),
            ExportColumn::make('name'),
            ExportColumn::make('firm_name'),
            ExportColumn::make('mobile'),
            ExportColumn::make('email'),
            ExportColumn::make('state'),
            ExportColumn::make('district'),
            ExportColumn::make('city'),
            ExportColumn::make('investment_range'),
            ExportColumn::make('years_in_business'),
            ExportColumn::make('current_business'),
            ExportColumn::make('has_godown'),
            ExportColumn::make('message'),
            ExportColumn::make('status')
                ->formatStateUsing(fn ($state) => $state?->label()),
            ExportColumn::make('admin_notes'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your business enquiry export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
