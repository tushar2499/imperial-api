<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteSettingCollectionPublicResource extends JsonResource
{
    /**
     * Keys that should be cast to integer values.
     *
     * @var array<int, string>
     */
    private const INTEGER_CAST_KEYS = [];

    /**
     * Keys that contain image paths.
     *
     * @var array<int, string>
     */
    private const IMAGE_KEYS = [
        'website_logo',
        'website_favicon',
        'website_preloader',
        'website_footer_image',
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
        $settings = $this->transformImageUrls($settings);
        $settings = $this->castIntegerKeys($settings);

        $formattedSettings = [];
        foreach ($settings as $key => $value) {
            $newKey = str_starts_with($key, 'website_') ? substr($key, 8) : $key;
            $formattedSettings[$newKey] = $value;
        }

        return $formattedSettings;
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
     * Transform image paths to full asset URLs.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function transformImageUrls(array $settings): array
    {
        foreach (self::IMAGE_KEYS as $key) {
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
