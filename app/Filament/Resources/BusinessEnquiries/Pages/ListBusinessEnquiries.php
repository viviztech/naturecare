<?php

namespace App\Filament\Resources\BusinessEnquiries\Pages;

use App\Filament\Resources\BusinessEnquiries\BusinessEnquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessEnquiries extends ListRecords
{
    protected static string $resource = BusinessEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
