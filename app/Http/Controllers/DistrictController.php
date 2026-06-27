<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\District\DistrictDestroyRequest;
use App\Http\Requests\Api\District\DistrictIndexRequest;
use App\Http\Requests\Api\District\DistrictShowRequest;
use App\Http\Requests\Api\District\DistrictStoreRequest;
use App\Http\Requests\Api\District\DistrictUpdateRequest;
use App\Http\Resources\DistrictResource;
use App\Services\DistrictService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DistrictController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly DistrictService $districtService) {}

    /**
     * Display a paginated listing of all districts.
     *
     * @param  DistrictIndexRequest  $request
     * @return JsonResponse
     */
    public function index(DistrictIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $districts = $this->districtService->pagination($attributes);

        return $this->successResponse(
            DistrictResource::collection($districts)->resolve(),
            'Districts retrieved successfully',
            $this->preparePaginator($districts)
        );
    }

    /**
     * Display all active without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $districts = $this->districtService->allActive();

        return $this->successResponse(
            DistrictResource::collection($districts)->resolve(),
            'All active districts retrieved successfully'
        );
    }

    /**
     * Store a newly created district.
     *
     * @param  DistrictStoreRequest  $request
     * @return JsonResponse
     */
    public function store(DistrictStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $district = $this->districtService->store($attributes);

        return $this->createdResponse(new DistrictResource($district), 'District created successfully');
    }

    /**
     * Display the specified district.
     *
     * @param  DistrictShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(DistrictShowRequest $request, int $id): JsonResponse
    {
        $district = $this->districtService->findById($id);

        return $this->successResponse(new DistrictResource($district), 'District retrieved successfully');
    }

    /**
     * Update the specified district.
     *
     * @param  DistrictUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(DistrictUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $district = $this->districtService->update($id, $attributes);

        return $this->successResponse(new DistrictResource($district), 'District updated successfully');
    }

    /**
     * Set the specified district to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $district = $this->districtService->activeById($id);

        return $this->successResponse(new DistrictResource($district), 'District activated successfully');
    }

    /**
     * Set the specified district to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $district = $this->districtService->inactiveById($id);

        return $this->successResponse(new DistrictResource($district), 'District deactivated successfully');
    }

    /**
     * Remove the specified district.
     *
     * @param  DistrictDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(DistrictDestroyRequest $request, int $id): JsonResponse
    {
        $this->districtService->destroy($id);

        return $this->successResponse([], 'District deleted successfully');
    }
}
