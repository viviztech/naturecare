<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_phone' => Setting::get('site_phone'),
            'site_whatsapp' => Setting::get('site_whatsapp'),
            'site_email' => Setting::get('site_email'),
            'admin_notification_email' => Setting::get('admin_notification_email'),
            'site_address' => Setting::get('site_address'),
            'google_map_embed_url' => Setting::get('google_map_embed_url'),
            'facebook_url' => Setting::get('facebook_url'),
            'instagram_url' => Setting::get('instagram_url'),
            'youtube_url' => Setting::get('youtube_url'),
            'meta_pixel_id' => Setting::get('meta_pixel_id'),
            'gstin' => Setting::get('gstin'),
            'gst_rate_percent' => Setting::get('gst_rate_percent', 18),
            'cod_handling_fee' => Setting::get('cod_handling_fee', 0),
            'default_shipping_charge' => Setting::get('default_shipping_charge', 0),
            'low_stock_threshold' => Setting::get('low_stock_threshold', 10),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Details')
                    ->columns(2)
                    ->components([
                        TextInput::make('site_phone')
                            ->label('Phone Number'),
                        TextInput::make('site_whatsapp')
                            ->label('WhatsApp Number')
                            ->helperText('Digits only with country code, e.g. 919999999999'),
                        TextInput::make('site_email')
                            ->label('Public Email')
                            ->email(),
                        TextInput::make('admin_notification_email')
                            ->label('Admin Notification Email')
                            ->email()
                            ->helperText('Enquiry notifications are sent here.'),
                        Textarea::make('site_address')
                            ->label('Address')
                            ->columnSpanFull(),
                        TextInput::make('google_map_embed_url')
                            ->label('Google Map Embed URL')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Section::make('Social Links')
                    ->columns(3)
                    ->components([
                        TextInput::make('facebook_url')->url(),
                        TextInput::make('instagram_url')->url(),
                        TextInput::make('youtube_url')->url(),
                    ]),

                Section::make('Tracking')
                    ->components([
                        TextInput::make('meta_pixel_id')
                            ->label('Meta Pixel ID')
                            ->helperText('Leave empty to disable Meta Pixel tracking.'),
                    ]),

                Section::make('Orders & Tax')
                    ->description('Razorpay API keys are configured via .env, not here — see README.')
                    ->columns(2)
                    ->components([
                        TextInput::make('gstin')
                            ->label('GSTIN')
                            ->maxLength(15)
                            ->helperText('Printed on GST invoices.'),
                        TextInput::make('gst_rate_percent')
                            ->label('GST Rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(18),
                        TextInput::make('cod_handling_fee')
                            ->label('COD Handling Fee (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₹'),
                        TextInput::make('default_shipping_charge')
                            ->label('Default Shipping Charge (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₹')
                            ->helperText('Used only as a fallback if no shipping zone matches — normally each zone sets its own charge.'),
                        TextInput::make('low_stock_threshold')
                            ->label('Low Stock Threshold (units)')
                            ->numeric()
                            ->minValue(0)
                            ->default(10)
                            ->helperText('Variants at or below this stock level are flagged on the dashboard.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
