<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\SeatPlan\SeatPlanDestroyRequest;
use App\Http\Requests\Api\SeatPlan\SeatPlanIndexRequest;
use App\Http\Requests\Api\SeatPlan\SeatPlanShowRequest;
use App\Http\Requests\Api\SeatPlan\SeatPlanStoreRequest;
use App\Http\Requests\Api\SeatPlan\SeatPlanUpdateRequest;
use App\Http\Resources\SeatPlanResource;
use App\Services\SeatPlanService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SeatPlanController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SeatPlanService $seatPlanService) {}

    /**
     * Display a paginated listing of all seat plans.
     *
     * @param  SeatPlanIndexRequest  $request
     * @return JsonResponse
     */
    public function index(SeatPlanIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $seatPlans = $this->seatPlanService->pagination($attributes);

        return $this->successResponse(
            SeatPlanResource::collection($seatPlans)->resolve(),
            'Seat plans retrieved successfully',
            $this->preparePaginator($seatPlans)
        );
    }

    /**
     * Display all active seat plans without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $seatPlans = $this->seatPlanService->allActive();

        return $this->successResponse(
            SeatPlanResource::collection($seatPlans)->resolve(),
            'All active seat plans retrieved successfully'
        );
    }

    /**
     * Store a newly created seat plan with floors and seats.
     *
     * @param  SeatPlanStoreRequest  $request
     * @return JsonResponse
     */
    public function store(SeatPlanStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $seatPlan = $this->seatPlanService->store($attributes);

        return $this->createdResponse(new SeatPlanResource($seatPlan), 'Seat plan created successfully');
    }

    /**
     * Display the specified seat plan with its floors and seats.
     *
     * @param  SeatPlanShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(SeatPlanShowRequest $request, int $id): JsonResponse
    {
        $seatPlan = $this->seatPlanService->findByIdWithFloors($id);

        return $this->successResponse(new SeatPlanResource($seatPlan), 'Seat plan retrieved successfully');
    }

    /**
     * Update the specified seat plan with its floors and seats.
     *
     * @param  SeatPlanUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(SeatPlanUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $seatPlan = $this->seatPlanService->update($id, $attributes);

        return $this->successResponse(new SeatPlanResource($seatPlan), 'Seat plan updated successfully');
    }

    /**
     * Set the specified seat plan to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $seatPlan = $this->seatPlanService->activeById($id);

        return $this->successResponse(new SeatPlanResource($seatPlan), 'Seat plan activated successfully');
    }

    /**
     * Set the specified seat plan to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $seatPlan = $this->seatPlanService->inactiveById($id);

        return $this->successResponse(new SeatPlanResource($seatPlan), 'Seat plan deactivated successfully');
    }

    /**
     * Remove the specified seat plan.
     *
     * @param  SeatPlanDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(SeatPlanDestroyRequest $request, int $id): JsonResponse
    {
        $this->seatPlanService->destroy($id);

        return $this->successResponse([], 'Seat plan deleted successfully');
    }
}
