<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Counter\CounterDestroyRequest;
use App\Http\Requests\Api\Counter\CounterIndexRequest;
use App\Http\Requests\Api\Counter\CounterShowRequest;
use App\Http\Requests\Api\Counter\CounterStoreRequest;
use App\Http\Requests\Api\Counter\CounterUpdateRequest;
use App\Http\Resources\CounterResource;
use App\Services\CounterService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CounterController extends Controller
{
    use ApiResponse;

    /**
     * Create a new controller instance.
     *
     * @param  CounterService  $counterService
     */
    public function __construct(private readonly CounterService $counterService) {}

    /**
     * Display a paginated listing of all counters.
     *
     * @param  CounterIndexRequest  $request
     * @return JsonResponse
     */
    public function index(CounterIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $counters = $this->counterService->pagination($attributes);

        return $this->successResponse(
            CounterResource::collection($counters)->resolve(),
            'Counters retrieved successfully',
            $this->preparePaginator($counters)
        );
    }

    /**
     * Display all active counters without pagination.
     *
     * @return JsonResponse
     */
    public function allActiveCounters(): JsonResponse
    {
        $counters = $this->counterService->allActive();

        return $this->successResponse(
            CounterResource::collection($counters)->resolve(),
            'All Active Counters retrieved successfully'
        );
    }

    /**
     * Store a newly created counter.
     *
     * @param  CounterStoreRequest  $request
     * @return JsonResponse
     */
    public function store(CounterStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $counter = $this->counterService->store($attributes);

        return $this->createdResponse(new CounterResource($counter), 'Counter created successfully');
    }

    /**
     * Display the specified counter.
     *
     * @param  CounterShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(CounterShowRequest $request, int $id): JsonResponse
    {
        $counter = $this->counterService->findById($id);

        return $this->successResponse(new CounterResource($counter), 'Counter retrieved successfully');
    }

    /**
     * Update the specified counter.
     *
     * @param  CounterUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(CounterUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $counter = $this->counterService->update($id, $attributes);

        return $this->successResponse(new CounterResource($counter), 'Counter updated successfully');
    }

    /**
     * Set the specified counter to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $counter = $this->counterService->activeById($id);

        return $this->successResponse(new CounterResource($counter), 'Counter activated successfully');
    }

    /**
     * Set the specified counter to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $counter = $this->counterService->inactiveById($id);

        return $this->successResponse(new CounterResource($counter), 'Counter deactivated successfully');
    }

    /**
     * Remove the specified counter.
     *
     * @param  CounterDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(CounterDestroyRequest $request, int $id): JsonResponse
    {
        $this->counterService->destroy($id);

        return $this->successResponse([], 'Counter deleted successfully');
    }
}
