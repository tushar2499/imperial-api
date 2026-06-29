<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Schedule\ScheduleDestroyRequest;
use App\Http\Requests\Api\Schedule\ScheduleIndexRequest;
use App\Http\Requests\Api\Schedule\ScheduleShowRequest;
use App\Http\Requests\Api\Schedule\ScheduleStoreRequest;
use App\Http\Requests\Api\Schedule\ScheduleUpdateRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ScheduleService $scheduleService) {}

    /**
     * Display a paginated listing of all schedules.
     *
     * @param  ScheduleIndexRequest  $request
     * @return JsonResponse
     */
    public function index(ScheduleIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $schedules = $this->scheduleService->pagination($attributes);

        return $this->successResponse(
            ScheduleResource::collection($schedules)->resolve(),
            'Schedules retrieved successfully',
            $this->preparePaginator($schedules)
        );
    }

    /**
     * Display all active schedules without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $schedules = $this->scheduleService->allActive();

        return $this->successResponse(
            ScheduleResource::collection($schedules)->resolve(),
            'All active schedules retrieved successfully'
        );
    }

    /**
     * Store a newly created schedule.
     *
     * @param  ScheduleStoreRequest  $request
     * @return JsonResponse
     */
    public function store(ScheduleStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $schedule = $this->scheduleService->store($attributes);

        return $this->createdResponse(new ScheduleResource($schedule), 'Schedule created successfully');
    }

    /**
     * Display the specified schedule.
     *
     * @param  ScheduleShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(ScheduleShowRequest $request, int $id): JsonResponse
    {
        $schedule = $this->scheduleService->findById($id);

        return $this->successResponse(new ScheduleResource($schedule), 'Schedule retrieved successfully');
    }

    /**
     * Update the specified schedule.
     *
     * @param  ScheduleUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(ScheduleUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $schedule = $this->scheduleService->update($id, $attributes);

        return $this->successResponse(new ScheduleResource($schedule), 'Schedule updated successfully');
    }

    /**
     * Set the specified schedule to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $schedule = $this->scheduleService->activeById($id);

        return $this->successResponse(new ScheduleResource($schedule), 'Schedule activated successfully');
    }

    /**
     * Set the specified schedule to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $schedule = $this->scheduleService->inactiveById($id);

        return $this->successResponse(new ScheduleResource($schedule), 'Schedule deactivated successfully');
    }

    /**
     * Remove the specified schedule.
     *
     * @param  ScheduleDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(ScheduleDestroyRequest $request, int $id): JsonResponse
    {
        $this->scheduleService->destroy($id);

        return $this->successResponse([], 'Schedule deleted successfully');
    }
}
