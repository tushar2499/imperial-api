<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Coach\CoachDestroyRequest;
use App\Http\Requests\Api\Coach\CoachIndexRequest;
use App\Http\Requests\Api\Coach\CoachShowRequest;
use App\Http\Requests\Api\Coach\CoachStoreRequest;
use App\Http\Requests\Api\Coach\CoachUpdateRequest;
use App\Http\Resources\CoachResource;
use App\Services\CoachService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CoachController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CoachService $coachService) {}

    /**
     * Display a paginated listing of all coaches.
     *
     * @param  CoachIndexRequest  $request
     * @return JsonResponse
     */
    public function index(CoachIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $coaches = $this->coachService->pagination($attributes);

        return $this->successResponse(
            CoachResource::collection($coaches)->resolve(),
            'Coaches retrieved successfully',
            $this->preparePaginator($coaches)
        );
    }

    /**
     * Display all active without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $coaches = $this->coachService->allActive();

        return $this->successResponse(
            CoachResource::collection($coaches)->resolve(),
            'All active coaches retrieved successfully'
        );
    }

    /**
     * Store a newly created coach.
     *
     * @param  CoachStoreRequest  $request
     * @return JsonResponse
     */
    public function store(CoachStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $coach = $this->coachService->store($attributes);

        return $this->createdResponse(new CoachResource($coach), 'Coach created successfully');
    }

    /**
     * Display the specified coach.
     *
     * @param  CoachShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(CoachShowRequest $request, int $id): JsonResponse
    {
        $coach = $this->coachService->findById($id);

        return $this->successResponse(new CoachResource($coach), 'Coach retrieved successfully');
    }

    /**
     * Update the specified coach.
     *
     * @param  CoachUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(CoachUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $coach = $this->coachService->update($id, $attributes);

        return $this->successResponse(new CoachResource($coach), 'Coach updated successfully');
    }

    /**
     * Set the specified coach to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $coach = $this->coachService->activeById($id);

        return $this->successResponse(new CoachResource($coach), 'Coach activated successfully');
    }

    /**
     * Set the specified coach to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $coach = $this->coachService->inactiveById($id);

        return $this->successResponse(new CoachResource($coach), 'Coach deactivated successfully');
    }

    /**
     * Remove the specified coach.
     *
     * @param  CoachDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(CoachDestroyRequest $request, int $id): JsonResponse
    {
        $this->coachService->destroy($id);

        return $this->successResponse([], 'Coach deleted successfully');
    }
}
