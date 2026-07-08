<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\SystemInfo\SystemInfoIndexRequest;
use App\Http\Resources\SystemSettingCollectionResource;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemInfoController extends Controller
{
    use ApiResponse;

    /**
     * SystemInfoController constructor.
     */
    public function __construct(protected SystemSettingService $systemSettingService) {}

    /**
     * Retrieve all public system information.
     */
    public function index(SystemInfoIndexRequest $request): JsonResponse
    {
        $systemSettings = $this->systemSettingService->all();
        $settings = (new SystemSettingCollectionResource($systemSettings))
            ->withExpectedKeys(SystemSetting::$settingsAttributes)
            ->toArray($request);

        return $this->successResponse($settings, 'System Information data successfully');
    }
}
