<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemSettingCollectionResource extends JsonResource
{
    /**
     * Keys that should be cast to integer values.
     *
     * @var array<int, string>
     */
    private const INTEGER_CAST_KEYS = [
        'data_per_page',
        'currency_decimal_point',
        'is_qr_code_show',
        'seat_hold_minutes',
        'booking_advance_days',
    ];

    /**
     * Expected setting keys with null defaults.
     *
     * @var array<int, string>
     */
    private array $expectedKeys = [];

    /**
     * Set the expected keys for null defaults.
     *
     * @param  array<int, string>  $keys
     */
    public function withExpectedKeys(array $keys): self
    {
        $this->expectedKeys = $keys;

        return $this;
    }

    /**
     * Transform the resource into a key-value array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $settings = collect($this->resource)
            ->pluck('value', 'key')
            ->toArray();

        $settings = $this->ensureExpectedKeys($settings);
        $settings = $this->transformLogoUrl($settings);
        $settings = $this->castIntegerKeys($settings);

        return $settings;
    }

    /**
     * Ensure all expected keys exist with null defaults.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function ensureExpectedKeys(array $settings): array
    {
        foreach ($this->expectedKeys as $key) {
            if (! array_key_exists($key, $settings)) {
                $settings[$key] = null;
            }
        }

        return $settings;
    }

    /**
     * Transform logo path to full asset URL.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function transformLogoUrl(array $settings): array
    {
        foreach (['logo', 'favicon', 'preloader', 'print_logo'] as $key) {
            if (! empty($settings[$key])) {
                $settings[$key] = asset($settings[$key]);
            }
        }

        return $settings;
    }

    /**
     * Cast specified keys to integer values.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function castIntegerKeys(array $settings): array
    {
        foreach (self::INTEGER_CAST_KEYS as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = (int) $settings[$key];
            }
        }

        return $settings;
    }
}
