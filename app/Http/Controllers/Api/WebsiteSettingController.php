<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WebsiteSetting\WebsiteSettingIndexRequest;
use App\Http\Requests\Api\WebsiteSetting\WebsiteSettingUpdateRequest;
use App\Http\Resources\WebsiteSettingCollectionResource;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebsiteSettingController extends Controller
{
    use ApiResponse;

    /**
     * WebsiteSettingController constructor.
     */
    public function __construct(protected SystemSettingService $systemSettingService) {}

    /**
     * Retrieve all website settings.
     */
    public function index(WebsiteSettingIndexRequest $request): JsonResponse
    {
        $systemSettings = $this->systemSettingService->allWebsiteSettings();
        $settings = (new WebsiteSettingCollectionResource($systemSettings))
            ->withExpectedKeys(SystemSetting::$websiteSettingsAttributes)
            ->toArray($request);

        return $this->successResponse($settings, 'Website settings retrieved successfully.');
    }

    /**
     * Update website settings.
     */
    public function update(WebsiteSettingUpdateRequest $request): JsonResponse
    {
        try {
            $this->systemSettingService->updateWebsiteSettings($request->validated());

            return $this->successResponse(null, 'Website settings updated successfully.');
        } catch (\Exception $exception) {
            return $this->errorResponse('Failed to update website settings: '.$exception->getMessage());
        }
    }
}
