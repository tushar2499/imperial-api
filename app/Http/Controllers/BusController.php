<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Bus\BusDestroyRequest;
use App\Http\Requests\Api\Bus\BusIndexRequest;
use App\Http\Requests\Api\Bus\BusShowRequest;
use App\Http\Requests\Api\Bus\BusStoreRequest;
use App\Http\Requests\Api\Bus\BusUpdateRequest;
use App\Http\Resources\BusResource;
use App\Services\BusService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BusController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BusService $busService) {}

    /**
     * Display a paginated listing of all buses.
     *
     * @param  BusIndexRequest  $request
     * @return JsonResponse
     */
    public function index(BusIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $buses = $this->busService->pagination($attributes);

        return $this->successResponse(
            BusResource::collection($buses)->resolve(),
            'Buses retrieved successfully',
            $this->preparePaginator($buses)
        );
    }

    /**
     * Store a newly created bus.
     *
     * @param  BusStoreRequest  $request
     * @return JsonResponse
     */
    public function store(BusStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $bus = $this->busService->store($attributes);

        return $this->createdResponse(new BusResource($bus), 'Bus created successfully');
    }

    /**
     * Display the specified bus.
     *
     * @param  BusShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(BusShowRequest $request, int $id): JsonResponse
    {
        $bus = $this->busService->findById($id);

        return $this->successResponse(new BusResource($bus), 'Bus retrieved successfully');
    }

    /**
     * Update the specified bus.
     *
     * @param  BusUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(BusUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $bus = $this->busService->update($id, $attributes);

        return $this->successResponse(new BusResource($bus), 'Bus updated successfully');
    }

    /**
     * Remove the specified bus.
     *
     * @param  BusDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(BusDestroyRequest $request, int $id): JsonResponse
    {
        $this->busService->destroy($id);

        return $this->successResponse([], 'Bus deleted successfully');
    }
}
