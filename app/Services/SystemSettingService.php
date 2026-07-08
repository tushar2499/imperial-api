<?php

namespace App\Services;

use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\DB;

class SystemSettingService
{
    /**
     * SystemSettingService constructor.
     */
    public function __construct() {}

    /**
     * All system setting by attributes
     */
    public function all(array $attributes = []): ?array
    {
        $query = SystemSetting::query()->whereIn('key', SystemSetting::$settingsAttributes);

        return $query->get()->toArray();
    }

    /**
     * All system setting by attributes for public
     */
    public function allForPublic(array $attributes = []): ?array
    {
        $systemSettings = SystemSetting::query()->whereIn('key', SystemSetting::$settingsAttributesForPublic)->get()->toArray();

        return $systemSettings;
    }

    /**
     * All website settings
     */
    public function allWebsiteSettings(array $attributes = []): ?array
    {
        $systemSettings = SystemSetting::query()->whereIn('key', SystemSetting::$websiteSettingsAttributes)->get()->toArray();

        return $systemSettings;
    }

    public function getDateFormat(): string
    {
        return $this->getSystemSetting('date_format', 'd-m-Y');
    }

    public function getTimeFormat(): string
    {
        return $this->getSystemSetting('time_format', 'h:i A');
    }

    private function getSystemSetting($key, $default = null)
    {
        static $settings = null;

        if ($settings === null) {
            $settings = SystemSetting::pluck('value', 'key')->toArray();
        }

        return $settings[$key] ?? $default;
    }

    /**
     * update system settings
     *
     * @throws Exception
     */
    public function updateSystemSettings(array $attributes): bool
    {
        $attributesWithoutLogo = $this->array_only($attributes, SystemSetting::$settingsAttributesWithoutLogo);

        try {
            DB::beginTransaction();

            // Handle removals of branding images
            foreach (['logo', 'favicon', 'preloader', 'print_logo'] as $imageKey) {
                $removeKey = 'remove_'.$imageKey;
                if (isset($attributes[$removeKey]) && $attributes[$removeKey] == '1') {
                    $systemSettingFile = SystemSetting::where('key', $imageKey)->first();
                    if ($systemSettingFile) {
                        if ($systemSettingFile->value && file_exists(public_path($systemSettingFile->value))) {
                            unlink(public_path($systemSettingFile->value));
                        }
                        $systemSettingFile->delete();
                    }
                }
            }

            foreach ($attributesWithoutLogo as $key => $value) {
                if (isset($value) && $value !== null && $value !== '') {
                    SystemSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                } else {
                    SystemSetting::where('key', $key)->delete();
                }
            }

            $this->uploadSystemFile('logo', $attributes);
            $this->uploadSystemFile('favicon', $attributes);
            $this->uploadSystemFile('preloader', $attributes);
            $this->uploadSystemFile('print_logo', $attributes);

            DB::commit();

            return true;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * update website settings
     *
     * @throws Exception
     */
    public function updateWebsiteSettings(array $attributes): bool
    {
        try {
            DB::beginTransaction();

            $websiteAttributesWithoutImage = SystemSetting::$websiteSettingsAttributesWithoutImage;

            foreach ($websiteAttributesWithoutImage as $dbKey) {
                $requestKey = str_starts_with($dbKey, 'website_') ? substr($dbKey, 8) : $dbKey;

                if (array_key_exists($requestKey, $attributes)) {
                    $value = $attributes[$requestKey];
                    if ($value) {
                        SystemSetting::updateOrCreate(
                            ['key' => $dbKey],
                            ['value' => $value]
                        );
                    } else {
                        SystemSetting::where('key', $dbKey)->delete();
                    }
                }
            }

            // Handle removals of branding images
            foreach (['logo', 'favicon', 'preloader', 'footer_image'] as $imageKey) {
                $removeKey = 'remove_'.$imageKey;

                if (isset($attributes[$removeKey]) && $attributes[$removeKey] == '1') {
                    $dbKey = 'website_'.$imageKey;
                    $systemSettingFile = SystemSetting::where('key', $dbKey)->first();
                    if ($systemSettingFile) {
                        if ($systemSettingFile->value && file_exists(public_path($systemSettingFile->value))) {
                            unlink(public_path($systemSettingFile->value));
                        }
                        $systemSettingFile->delete();
                    }
                }
            }

            $this->uploadWebsiteFile('website_logo', 'logo', $attributes);
            $this->uploadWebsiteFile('website_favicon', 'favicon', $attributes);
            $this->uploadWebsiteFile('website_preloader', 'preloader', $attributes);
            $this->uploadWebsiteFile('website_footer_image', 'footer_image', $attributes);

            DB::commit();

            return true;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function array_only(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * uploadSystemFile
     */
    private function uploadSystemFile(string $keyName, array $attributes): void
    {
        if (isset($attributes[$keyName])) {
            $systemSettingFile = SystemSetting::where('key', $keyName)->first();

            $logoPath = file_uploaded(request()->file($keyName), 'system-settings');

            if ($systemSettingFile) {
                if ($systemSettingFile->value && file_exists(public_path($systemSettingFile->value))) {
                    unlink(public_path($systemSettingFile->value));
                }
            }

            SystemSetting::updateOrCreate(
                ['key' => $keyName],
                ['value' => $logoPath]
            );
        }
    }

    /**
     * uploadWebsiteFile
     */
    private function uploadWebsiteFile(string $keyName, string $fileName, array $attributes): void
    {
        if (isset($attributes[$fileName])) {
            $websiteSettingFile = SystemSetting::where('key', $keyName)->first();

            $logoPath = file_uploaded(request()->file($fileName), 'system-settings');

            if ($websiteSettingFile) {
                if ($websiteSettingFile->value && file_exists(public_path($websiteSettingFile->value))) {
                    unlink(public_path($websiteSettingFile->value));
                }
            }

            if ($logoPath) {
                SystemSetting::updateOrCreate(
                    ['key' => $keyName],
                    ['value' => $logoPath]
                );
            }
        }
    }
}
