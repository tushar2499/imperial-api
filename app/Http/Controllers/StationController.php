<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Station\StationDestroyRequest;
use App\Http\Requests\Api\Station\StationIndexRequest;
use App\Http\Requests\Api\Station\StationShowRequest;
use App\Http\Requests\Api\Station\StationStoreRequest;
use App\Http\Requests\Api\Station\StationUpdateRequest;
use App\Http\Resources\StationResource;
use App\Services\StationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class StationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly StationService $stationService) {}

    /**
     * Display a paginated listing of all stations.
     *
     * @param  StationIndexRequest  $request
     * @return JsonResponse
     */
    public function index(StationIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $stations = $this->stationService->pagination($attributes);

        return $this->successResponse(
            StationResource::collection($stations)->resolve(),
            'Stations retrieved successfully',
            $this->preparePaginator($stations)
        );
    }

    /**
     * Store multiple newly created stations (one per district ID).
     *
     * @param  StationStoreRequest  $request
     * @return JsonResponse
     */
    public function store(StationStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $stations = $this->stationService->store($attributes);

        return $this->createdResponse(
            StationResource::collection($stations)->resolve(),
            'Stations created successfully'
        );
    }

    /**
     * Display the specified station.
     *
     * @param  StationShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(StationShowRequest $request, int $id): JsonResponse
    {
        $station = $this->stationService->findById($id);

        return $this->successResponse(new StationResource($station), 'Station retrieved successfully');
    }

    /**
     * Update the specified station.
     *
     * @param  StationUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(StationUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $station = $this->stationService->update($id, $attributes);

        return $this->successResponse(new StationResource($station), 'Station updated successfully');
    }

    /**
     * Remove the specified station.
     *
     * @param  StationDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(StationDestroyRequest $request, int $id): JsonResponse
    {
        $this->stationService->destroy($id);

        return $this->successResponse([], 'Station deleted successfully');
    }
}
