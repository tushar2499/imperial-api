<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'key' => 'string',
        'value' => 'string',
    ];

    public static $settingsAttributes = [
        'name',
        'email',
        'phone',
        'address',
        'print_footer_message',
        'bin_number',
        'logo',
        'favicon',
        'preloader',
        'print_logo',
        'data_per_page',
        'currency_symbol',
        'currency_name',
        'currency_position',
        'currency_decimal_point',
        'date_format', // d-m-Y, m-d-Y, Y-m-d, d/m/Y, m/d/Y, Y/m/d, d.m.Y, M d Y, F d Y
        'time_format', // h:i A, h:i:s A, H:i, H:i:s
        'is_qr_code_show',      // 1 = show QR code on printed ticket, 0 = hide
        'seat_hold_minutes',    // default seat hold duration in minutes before auto-release
        'booking_advance_days', // max days in advance a passenger can book a ticket
    ];

    public static $settingsAttributesWithoutLogo = [
        'name',
        'email',
        'phone',
        'address',
        'print_footer_message',
        'bin_number',
        'data_per_page',
        'currency_symbol',
        'currency_name',
        'currency_position',
        'currency_decimal_point',
        'date_format', // d-m-Y, m-d-Y, Y-m-d, d/m/Y, m/d/Y, Y/m/d, d.m.Y, M d Y, F d Y
        'time_format', // h:i A, h:i:s A, H:i, H:i:s
        'is_qr_code_show',
        'seat_hold_minutes',
        'booking_advance_days',
    ];

    public static $settingsAttributesForPublic = [
        'website_name',
        'website_email',
        'website_phone',
        'website_address',
        'website_opening_time',
        'website_closing_time',
        'website_google_map',
        'website_copyright',
        'website_footer_text',
        'website_facebook_link',
        'website_twitter_link',
        'website_instagram_link',
        'website_youtube_link',
        'website_tiktok_link',
        'website_whatsapp_link',
        'website_logo',
        'website_favicon',
        'website_preloader',
        'website_footer_image',
        'website_currency_symbol',
        'website_currency_name',
        'website_currency_position',
        'website_currency_decimal_point',
    ];

    public static $websiteSettingsAttributes = [
        'website_name',
        'website_email',
        'website_phone',
        'website_address',
        'website_opening_time',
        'website_closing_time',
        'website_google_map',
        'website_copyright',
        'website_footer_text',
        'website_facebook_link',
        'website_twitter_link',
        'website_instagram_link',
        'website_youtube_link',
        'website_tiktok_link',
        'website_whatsapp_link',
        'website_logo',
        'website_favicon',
        'website_preloader',
        'website_footer_image',
        'website_currency_symbol',
        'website_currency_name',
        'website_currency_position',
        'website_currency_decimal_point',
    ];

    public static $websiteSettingsAttributesWithoutImage = [
        'website_name',
        'website_email',
        'website_phone',
        'website_address',
        'website_opening_time',
        'website_closing_time',
        'website_google_map',
        'website_copyright',
        'website_footer_text',
        'website_facebook_link',
        'website_twitter_link',
        'website_instagram_link',
        'website_youtube_link',
        'website_tiktok_link',
        'website_whatsapp_link',
        'website_currency_symbol',
        'website_currency_name',
        'website_currency_position',
        'website_currency_decimal_point',
    ];
}
