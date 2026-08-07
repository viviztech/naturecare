<?php

namespace App\Filament\Resources\BusinessEnquiries\Schemas;

use App\Enums\EnquiryStatus;
use App\Enums\PartnerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessEnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enquiry Details')
                    ->columns(2)
                    ->components([
                        Select::make('partner_type')
                            ->options(PartnerType::class)
                            ->required(),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('firm_name')
                            ->required(),
                        TextInput::make('mobile')
                            ->required()
                            ->tel(),
                        TextInput::make('email')
                            ->email(),
                        Select::make('state')
                            ->options(array_combine(config('naturecare.indian_states'), config('naturecare.indian_states')))
                            ->searchable()
                            ->required(),
                        TextInput::make('district')
                            ->required(),
                        TextInput::make('city')
                            ->required(),
                        Select::make('investment_range')
                            ->options(config('naturecare.investment_ranges'))
                            ->required(),
                        Select::make('years_in_business')
                            ->options(config('naturecare.years_in_business_ranges'))
                            ->required(),
                        Select::make('current_business')
                            ->options(config('naturecare.business_types'))
                            ->required(),
                        Toggle::make('has_godown'),
                        Textarea::make('message')
                            ->columnSpanFull(),
                    ]),

                Section::make('Admin')
                    ->columns(2)
                    ->components([
                        Select::make('status')
                            ->options(EnquiryStatus::class)
                            ->default(EnquiryStatus::New->value)
                            ->required(),
                        Textarea::make('admin_notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
