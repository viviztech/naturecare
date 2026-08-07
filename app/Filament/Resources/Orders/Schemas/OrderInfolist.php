<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(3)
                    ->components([
                        TextEntry::make('order_number'),
                        TextEntry::make('order_status')
                            ->badge(),
                        TextEntry::make('payment_status')
                            ->badge(),
                        TextEntry::make('payment_method')
                            ->badge(),
                        TextEntry::make('gst_invoice_number')
                            ->label('GST Invoice #')
                            ->placeholder('Not yet generated'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ]),

                Section::make('Customer & Delivery')
                    ->columns(2)
                    ->components([
                        TextEntry::make('contact_name')
                            ->label('Name'),
                        TextEntry::make('contact_mobile')
                            ->label('Mobile')
                            ->url(fn ($record) => 'https://wa.me/91'.$record->contact_mobile)
                            ->openUrlInNewTab(),
                        TextEntry::make('contact_email')
                            ->label('Email')
                            ->placeholder('-'),
                        TextEntry::make('shipping_pincode')
                            ->label('Pincode'),
                        TextEntry::make('formatted_shipping_address')
                            ->label('Address')
                            // ->state() (not ->formatStateUsing()) so Filament never reads
                            // the raw `shipping_address` array attribute directly — doing so
                            // makes TextEntry treat it as a multi-value list and format each
                            // array element separately instead of the whole address once.
                            ->state(function ($record) {
                                $address = $record->shipping_address ?? [];

                                $lines = array_filter([
                                    $address['line1'] ?? null,
                                    $address['line2'] ?? null,
                                ]);

                                return trim(implode(', ', $lines).', '.($address['city'] ?? '').', '.$record->shipping_state);
                            })
                            ->columnSpanFull(),
                        TextEntry::make('tracking_number')
                            ->placeholder('-'),
                        TextEntry::make('tracking_url')
                            ->label('Tracking Link')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->tracking_url)
                            ->openUrlInNewTab(),
                    ]),

                Section::make('Items')
                    ->components([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product_name_snapshot')
                                    ->label('Product'),
                                TextEntry::make('size_label_snapshot')
                                    ->label('Size'),
                                TextEntry::make('qty')
                                    ->label('Qty'),
                                TextEntry::make('line_total')
                                    ->label('Amount')
                                    ->formatStateUsing(fn ($state) => $state?->format()),
                            ]),
                    ]),

                Section::make('Totals')
                    ->columns(3)
                    ->components([
                        TextEntry::make('subtotal')
                            ->formatStateUsing(fn ($state) => $state?->format()),
                        TextEntry::make('discount_total')
                            ->label('Discount')
                            ->formatStateUsing(fn ($state) => $state?->format()),
                        TextEntry::make('shipping_charge')
                            ->label('Shipping')
                            ->formatStateUsing(fn ($state) => $state?->format()),
                        TextEntry::make('tax_cgst')
                            ->label('CGST')
                            ->formatStateUsing(fn ($state) => $state?->format()),
                        TextEntry::make('tax_sgst')
                            ->label('SGST')
                            ->formatStateUsing(fn ($state) => $state?->format()),
                        TextEntry::make('tax_igst')
                            ->label('IGST')
                            ->formatStateUsing(fn ($state) => $state?->format()),
                        TextEntry::make('total')
                            ->formatStateUsing(fn ($state) => $state?->format())
                            ->weight('bold'),
                    ]),

                Section::make('Admin Notes')
                    ->collapsible()
                    ->components([
                        TextEntry::make('admin_notes')
                            ->hiddenLabel()
                            ->placeholder('No notes yet.'),
                        TextEntry::make('cancellation_reason')
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->cancellation_reason !== null),
                    ]),
            ]);
    }
}
