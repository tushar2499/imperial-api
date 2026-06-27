<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Designation\DesignationDestroyRequest;
use App\Http\Requests\Api\Designation\DesignationIndexRequest;
use App\Http\Requests\Api\Designation\DesignationShowRequest;
use App\Http\Requests\Api\Designation\DesignationStoreRequest;
use App\Http\Requests\Api\Designation\DesignationUpdateRequest;
use App\Http\Resources\DesignationResource;
use App\Services\DesignationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DesignationController extends Controller
{
    use ApiResponse;

    /**
     * Create a new controller instance.
     *
     * @param  DesignationService  $designationService
     */
    public function __construct(private readonly DesignationService $designationService) {}

    /**
     * Display a paginated listing of all designations.
     *
     * @param  DesignationIndexRequest  $request
     * @return JsonResponse
     */
    public function index(DesignationIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $designations = $this->designationService->pagination($attributes);

        return $this->successResponse(
            DesignationResource::collection($designations)->resolve(),
            'Designations retrieved successfully',
            $this->preparePaginator($designations)
        );
    }

    /**
     * Display all active designations without pagination.
     *
     * @return JsonResponse
     */
    public function allActiveDesignations(): JsonResponse
    {
        $designations = $this->designationService->allActive();

        return $this->successResponse(
            DesignationResource::collection($designations)->resolve(),
            'All Active Designations retrieved successfully'
        );
    }

    /**
     * Store a newly created designation.
     *
     * @param  DesignationStoreRequest  $request
     * @return JsonResponse
     */
    public function store(DesignationStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $designation = $this->designationService->store($attributes);

        return $this->createdResponse(new DesignationResource($designation), 'Designation created successfully');
    }

    /**
     * Display the specified designation.
     *
     * @param  DesignationShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(DesignationShowRequest $request, int $id): JsonResponse
    {
        $designation = $this->designationService->findById($id);

        return $this->successResponse(new DesignationResource($designation), 'Designation retrieved successfully');
    }

    /**
     * Update the specified designation.
     *
     * @param  DesignationUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(DesignationUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $designation = $this->designationService->update($id, $attributes);

        return $this->successResponse(new DesignationResource($designation), 'Designation updated successfully');
    }

    /**
     * Set the specified designation to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $designation = $this->designationService->activeById($id);

        return $this->successResponse(new DesignationResource($designation), 'Designation activated successfully');
    }

    /**
     * Set the specified designation to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $designation = $this->designationService->inactiveById($id);

        return $this->successResponse(new DesignationResource($designation), 'Designation deactivated successfully');
    }

    /**
     * Remove the specified designation.
     *
     * @param  DesignationDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(DesignationDestroyRequest $request, int $id): JsonResponse
    {
        $this->designationService->destroy($id);

        return $this->successResponse([], 'Designation deleted successfully');
    }
}
