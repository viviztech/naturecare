<?php

namespace App\Filament\Resources\BusinessEnquiries\Pages;

use App\Filament\Resources\BusinessEnquiries\BusinessEnquiryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessEnquiry extends ViewRecord
{
    protected static string $resource = BusinessEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
