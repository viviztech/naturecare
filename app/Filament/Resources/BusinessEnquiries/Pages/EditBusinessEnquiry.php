<?php

namespace App\Filament\Resources\BusinessEnquiries\Pages;

use App\Filament\Resources\BusinessEnquiries\BusinessEnquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessEnquiry extends EditRecord
{
    protected static string $resource = BusinessEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
