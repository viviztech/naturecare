<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_phone' => '+91 99999 99999',
            'site_whatsapp' => '919999999999',
            'site_email' => 'enquiry@naturecareplus.com',
            'admin_notification_email' => 'online@viviz.in',
            'site_address' => 'Nature Care Products, Industrial Estate, Chennai, Tamil Nadu, India',
            'google_map_embed_url' => 'https://www.google.com/maps?q=Chennai,Tamil+Nadu&output=embed',
            'facebook_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
            'meta_pixel_id' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
