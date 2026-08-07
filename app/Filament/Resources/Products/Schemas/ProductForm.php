<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Details')
                    ->columns(2)
                    ->components([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('short_description')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(5)
                            ->columnSpanFull(),
                        Textarea::make('usage_instructions')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('hsn_code')
                            ->label('HSN Code')
                            ->maxLength(20)
                            ->helperText('Used on GST invoices. Copied onto each order line item at the time of purchase.'),
                    ]),

                Section::make('Images')
                    ->components([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection(Product::MEDIA_COLLECTION)
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(6)
                            ->hiddenLabel(),
                    ]),

                Section::make('Flags')
                    ->columns(3)
                    ->components([
                        Toggle::make('is_featured')
                            ->helperText('Show on homepage featured section'),
                        Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Visible on the public site'),
                        Toggle::make('is_commercial')
                            ->helperText('Part of the Commercial Range'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        TextInput::make('meta_title')
                            ->maxLength(255),
                        TextInput::make('meta_description')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
