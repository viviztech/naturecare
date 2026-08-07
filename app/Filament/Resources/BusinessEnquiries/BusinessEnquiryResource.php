<?php

namespace App\Filament\Resources\BusinessEnquiries;

use App\Filament\Resources\BusinessEnquiries\Pages\CreateBusinessEnquiry;
use App\Filament\Resources\BusinessEnquiries\Pages\EditBusinessEnquiry;
use App\Filament\Resources\BusinessEnquiries\Pages\ListBusinessEnquiries;
use App\Filament\Resources\BusinessEnquiries\Pages\ViewBusinessEnquiry;
use App\Filament\Resources\BusinessEnquiries\Schemas\BusinessEnquiryForm;
use App\Filament\Resources\BusinessEnquiries\Schemas\BusinessEnquiryInfolist;
use App\Filament\Resources\BusinessEnquiries\Tables\BusinessEnquiriesTable;
use App\Models\BusinessEnquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusinessEnquiryResource extends Resource
{
    protected static ?string $model = BusinessEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'Enquiries';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->where('status', 'new')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return BusinessEnquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BusinessEnquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessEnquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessEnquiries::route('/'),
            'create' => CreateBusinessEnquiry::route('/create'),
            'view' => ViewBusinessEnquiry::route('/{record}'),
            'edit' => EditBusinessEnquiry::route('/{record}/edit'),
        ];
    }
}
