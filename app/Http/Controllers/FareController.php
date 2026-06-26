<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Fare\FareDestroyRequest;
use App\Http\Requests\Api\Fare\FareIndexRequest;
use App\Http\Requests\Api\Fare\FareShowRequest;
use App\Http\Requests\Api\Fare\FareStoreRequest;
use App\Http\Requests\Api\Fare\FareUpdateRequest;
use App\Http\Resources\FareResource;
use App\Services\FareService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FareController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FareService $fareService) {}

    /**
     * Display a paginated listing of all fares.
     *
     * @param  FareIndexRequest  $request
     * @return JsonResponse
     */
    public function index(FareIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $fares = $this->fareService->pagination($attributes);

        return $this->successResponse(
            FareResource::collection($fares)->resolve(),
            'Fares retrieved successfully',
            $this->preparePaginator($fares)
        );
    }

    /**
     * Store a newly created fare.
     *
     * @param  FareStoreRequest  $request
     * @return JsonResponse
     */
    public function store(FareStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $fare = $this->fareService->store($attributes);

        return $this->createdResponse(new FareResource($fare), 'Fare created successfully');
    }

    /**
     * Display the specified fare.
     *
     * @param  FareShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(FareShowRequest $request, int $id): JsonResponse
    {
        $fare = $this->fareService->findById($id);

        return $this->successResponse(new FareResource($fare), 'Fare retrieved successfully');
    }

    /**
     * Update the specified fare.
     *
     * @param  FareUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(FareUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $fare = $this->fareService->update($id, $attributes);

        return $this->successResponse(new FareResource($fare), 'Fare updated successfully');
    }

    /**
     * Remove the specified fare.
     *
     * @param  FareDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(FareDestroyRequest $request, int $id): JsonResponse
    {
        $this->fareService->destroy($id);

        return $this->successResponse([], 'Fare deleted successfully');
    }
}
