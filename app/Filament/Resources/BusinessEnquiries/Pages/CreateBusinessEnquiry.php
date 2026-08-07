<?php

namespace App\Filament\Resources\BusinessEnquiries\Pages;

use App\Filament\Resources\BusinessEnquiries\BusinessEnquiryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessEnquiry extends CreateRecord
{
    protected static string $resource = BusinessEnquiryResource::class;
}
