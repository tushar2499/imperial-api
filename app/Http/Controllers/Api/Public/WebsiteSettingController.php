<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\WebsiteSetting\WebsiteSettingIndexRequest;
use App\Http\Resources\Public\WebsiteSettingCollectionPublicResource;
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
     * Retrieve all public system settings.
     */
    public function index(WebsiteSettingIndexRequest $request): JsonResponse
    {
        $systemSettings = $this->systemSettingService->allForPublic();
        $settings = (new WebsiteSettingCollectionPublicResource($systemSettings))
            ->withExpectedKeys(SystemSetting::$settingsAttributesForPublic)
            ->toArray($request);

        return $this->successResponse($settings, 'Info retrieved successfully');
    }
}
