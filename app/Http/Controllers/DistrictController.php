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

    public function __construct(private readonly DistrictService $service) {}

    /**
     * Display a paginated listing of all districts.
     *
     * @param  DistrictIndexRequest  $request
     * @return JsonResponse
     */
    public function index(DistrictIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $districts = $this->service->pagination($attributes);

        return $this->successResponse(
            DistrictResource::collection($districts)->resolve(),
            'Districts retrieved successfully',
            $this->preparePaginator($districts)
        );
    }

    /**
     * Display all active districts without pagination.
     *
     * @return JsonResponse
     */
    public function allActiveDistricts(): JsonResponse
    {
        $districts = $this->service->allActive();

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
        $district = $this->service->store($attributes);

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
        $district = $this->service->findById($id);

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
        $district = $this->service->update($id, $attributes);

        return $this->successResponse(new DistrictResource($district), 'District updated successfully');
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
        $this->service->destroy($id);

        return $this->successResponse([], 'District deleted successfully');
    }
}
