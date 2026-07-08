<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'name', 'value' => 'Imperial Bus Ticketing'],
            ['key' => 'email', 'value' => 'admin@imperial.com'],
            ['key' => 'phone', 'value' => '+1234567890'],
            ['key' => 'address', 'value' => 'Imperial Bus Station, City'],
            ['key' => 'print_footer_message', 'value' => 'Thank you for travelling with Imperial Express!'],
            ['key' => 'data_per_page', 'value' => '10'],
            ['key' => 'currency_symbol', 'value' => '৳'],
            ['key' => 'currency_name', 'value' => 'Bangladeshi Taka'],
            ['key' => 'currency_position', 'value' => 'before'],
            ['key' => 'currency_decimal_point', 'value' => '2'],
            ['key' => 'date_format', 'value' => 'd-m-Y'],
            ['key' => 'time_format', 'value' => 'h:i A'],
            ['key' => 'is_qr_code_show', 'value' => '0'],
            ['key' => 'seat_hold_minutes', 'value' => '60'],
            ['key' => 'booking_advance_days', 'value' => '30'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
