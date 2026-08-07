<?php

namespace App\Filament\Resources\BusinessEnquiries\Tables;

use App\Enums\EnquiryStatus;
use App\Enums\PartnerType;
use App\Filament\Exports\BusinessEnquiryExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessEnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('partner_type')
                    ->badge(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('firm_name')
                    ->searchable(),
                TextColumn::make('mobile')
                    ->searchable(),
                TextColumn::make('state')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                IconColumn::make('has_godown')
                    ->boolean(),
                SelectColumn::make('status')
                    ->options(EnquiryStatus::class),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('partner_type')
                    ->options(PartnerType::class),
                SelectFilter::make('status')
                    ->options(EnquiryStatus::class),
                SelectFilter::make('state')
                    ->options(fn () => array_combine(config('naturecare.indian_states'), config('naturecare.indian_states')))
                    ->searchable(),
                Filter::make('created_at')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('created_from'),
                            DatePicker::make('created_until'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn ($record) => $record->whatsappUrl())
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(BusinessEnquiryExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(BusinessEnquiryExporter::class),
                ]),
            ]);
    }
}
