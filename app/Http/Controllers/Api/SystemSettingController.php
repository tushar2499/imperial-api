<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SystemSetting\SystemSettingIndexRequest;
use App\Http\Requests\Api\SystemSetting\SystemSettingUpdateRequest;
use App\Http\Resources\SystemSettingCollectionResource;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemSettingController extends Controller
{
    use ApiResponse;

    /**
     * SystemSettingController constructor.
     */
    public function __construct(protected SystemSettingService $systemSettingService) {}

    /**
     * Retrieve all system settings.
     */
    public function index(SystemSettingIndexRequest $request): JsonResponse
    {
        $systemSettings = $this->systemSettingService->all();
        $settings = (new SystemSettingCollectionResource($systemSettings))
            ->withExpectedKeys(SystemSetting::$settingsAttributes)
            ->toArray($request);

        return $this->successResponse($settings, 'System settings retrieved successfully.');
    }

    /**
     * Update system settings.
     */
    public function update(SystemSettingUpdateRequest $request): JsonResponse
    {
        try {
            $this->systemSettingService->updateSystemSettings($request->validated());

            return $this->successResponse(null, 'System settings updated successfully.');
        } catch (\Exception $exception) {
            return $this->errorResponse('Failed to update system settings: '.$exception->getMessage());
        }
    }
}
