<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\CoachConfiguration\CoachConfigurationDestroyRequest;
use App\Http\Requests\Api\CoachConfiguration\CoachConfigurationIndexRequest;
use App\Http\Requests\Api\CoachConfiguration\CoachConfigurationShowRequest;
use App\Http\Requests\Api\CoachConfiguration\CoachConfigurationStoreRequest;
use App\Http\Requests\Api\CoachConfiguration\CoachConfigurationUpdateRequest;
use App\Http\Resources\CoachConfigurationResource;
use App\Services\CoachConfigurationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CoachConfigurationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CoachConfigurationService $coachConfigurationService) {}

    /**
     * Display a paginated listing of all coach configurations.
     *
     * @param  CoachConfigurationIndexRequest  $request
     * @return JsonResponse
     */
    public function index(CoachConfigurationIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $configurations = $this->coachConfigurationService->pagination($attributes);

        return $this->successResponse(
            CoachConfigurationResource::collection($configurations)->resolve(),
            'Coach configurations retrieved successfully',
            $this->preparePaginator($configurations)
        );
    }

    /**
     * Display all active coach configurations without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $configurations = $this->coachConfigurationService->allActive();

        return $this->successResponse(
            CoachConfigurationResource::collection($configurations)->resolve(),
            'All active coach configurations retrieved successfully'
        );
    }

    /**
     * Store a newly created coach configuration with boarding/dropping points.
     *
     * @param  CoachConfigurationStoreRequest  $request
     * @return JsonResponse
     */
    public function store(CoachConfigurationStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $coachConfiguration = $this->coachConfigurationService->store($attributes);

        return $this->createdResponse(new CoachConfigurationResource($coachConfiguration), 'Coach configuration created successfully');
    }

    /**
     * Display the specified coach configuration.
     *
     * @param  CoachConfigurationShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(CoachConfigurationShowRequest $request, int $id): JsonResponse
    {
        $coachConfiguration = $this->coachConfigurationService->findById($id);

        return $this->successResponse(new CoachConfigurationResource($coachConfiguration), 'Coach configuration retrieved successfully');
    }

    /**
     * Update the specified coach configuration with boarding/dropping points.
     *
     * @param  CoachConfigurationUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(CoachConfigurationUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $coachConfiguration = $this->coachConfigurationService->update($id, $attributes);

        return $this->successResponse(new CoachConfigurationResource($coachConfiguration), 'Coach configuration updated successfully');
    }

    /**
     * Set the specified coach configuration to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $coachConfiguration = $this->coachConfigurationService->activeById($id);

        return $this->successResponse(new CoachConfigurationResource($coachConfiguration), 'Coach configuration activated successfully');
    }

    /**
     * Set the specified coach configuration to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $coachConfiguration = $this->coachConfigurationService->inactiveById($id);

        return $this->successResponse(new CoachConfigurationResource($coachConfiguration), 'Coach configuration deactivated successfully');
    }

    /**
     * Remove the specified coach configuration and its boarding/dropping points.
     *
     * @param  CoachConfigurationDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(CoachConfigurationDestroyRequest $request, int $id): JsonResponse
    {
        $this->coachConfigurationService->destroy($id);

        return $this->successResponse([], 'Coach configuration deleted successfully');
    }

    /**
     * Get coach configurations filtered by schedule.
     *
     * @param  int  $scheduleId
     * @return JsonResponse
     */
    public function getBySchedule(int $scheduleId): JsonResponse
    {
        $configurations = $this->coachConfigurationService->getBySchedule($scheduleId);

        return $this->successResponse(
            CoachConfigurationResource::collection($configurations)->resolve(),
            'Coach configurations retrieved successfully'
        );
    }

    /**
     * Get coach configurations filtered by coach.
     *
     * @param  int  $coachId
     * @return JsonResponse
     */
    public function getByCoach(int $coachId): JsonResponse
    {
        $configurations = $this->coachConfigurationService->getByCoach($coachId);

        return $this->successResponse(
            CoachConfigurationResource::collection($configurations)->resolve(),
            'Coach configurations retrieved successfully'
        );
    }

    /**
     * Get coach configurations filtered by route.
     *
     * @param  int  $routeId
     * @return JsonResponse
     */
    public function getByRoute(int $routeId): JsonResponse
    {
        $configurations = $this->coachConfigurationService->getByRoute($routeId);

        return $this->successResponse(
            CoachConfigurationResource::collection($configurations)->resolve(),
            'Coach configurations retrieved successfully'
        );
    }
}
