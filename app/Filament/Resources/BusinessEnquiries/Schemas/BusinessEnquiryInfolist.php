<?php

namespace App\Filament\Resources\BusinessEnquiries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessEnquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Partner Enquiry')
                    ->columns(2)
                    ->components([
                        TextEntry::make('partner_type')
                            ->badge(),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('name'),
                        TextEntry::make('firm_name'),
                        TextEntry::make('mobile')
                            ->url(fn ($record) => $record->whatsappUrl())
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-chat-bubble-left-right'),
                        TextEntry::make('email')
                            ->placeholder('-'),
                        TextEntry::make('city')
                            ->formatStateUsing(fn ($record) => "{$record->city}, {$record->district}, {$record->state}")
                            ->label('Location'),
                        TextEntry::make('investment_range')
                            ->formatStateUsing(fn ($state) => config('naturecare.investment_ranges')[$state] ?? $state),
                        TextEntry::make('years_in_business')
                            ->formatStateUsing(fn ($state) => config('naturecare.years_in_business_ranges')[$state] ?? $state),
                        TextEntry::make('current_business')
                            ->formatStateUsing(fn ($state) => config('naturecare.business_types')[$state] ?? $state),
                        IconEntry::make('has_godown')
                            ->boolean()
                            ->placeholder('-'),
                        TextEntry::make('message')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Admin')
                    ->columns(2)
                    ->components([
                        TextEntry::make('admin_notes')
                            ->placeholder('No notes yet.')
                            ->columnSpanFull(),
                        TextEntry::make('ip_address')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
