<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\TransportRoute\TransportRouteDestroyRequest;
use App\Http\Requests\Api\TransportRoute\TransportRouteIndexRequest;
use App\Http\Requests\Api\TransportRoute\TransportRouteShowRequest;
use App\Http\Requests\Api\TransportRoute\TransportRouteStoreRequest;
use App\Http\Requests\Api\TransportRoute\TransportRouteUpdatePopularPositionsRequest;
use App\Http\Requests\Api\TransportRoute\TransportRouteUpdateRequest;
use App\Http\Resources\TransportRouteResource;
use App\Services\TransportRouteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TransportRouteController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TransportRouteService $transportRouteService) {}

    /**
     * Display a paginated listing of transport routes.
     *
     * @param  TransportRouteIndexRequest  $request
     * @return JsonResponse
     */
    public function index(TransportRouteIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $routes = $this->transportRouteService->pagination($attributes);

        return $this->successResponse(
            TransportRouteResource::collection($routes)->resolve(),
            'Transport routes retrieved successfully',
            $this->preparePaginator($routes)
        );
    }

    /**
     * Display all popular transport routes ordered by position.
     *
     * @return JsonResponse
     */
    public function allPopularRoutes(): JsonResponse
    {
        $routes = $this->transportRouteService->allPopular();

        return $this->successResponse(
            TransportRouteResource::collection($routes)->resolve(),
            'Popular transport routes retrieved successfully'
        );
    }

    /**
     * Store a newly created transport route.
     *
     * @param  TransportRouteStoreRequest  $request
     * @return JsonResponse
     */
    public function store(TransportRouteStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $route = $this->transportRouteService->store($attributes);

        return $this->createdResponse(new TransportRouteResource($route), 'Transport route created successfully');
    }

    /**
     * Display the specified transport route.
     *
     * @param  TransportRouteShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(TransportRouteShowRequest $request, int $id): JsonResponse
    {
        $route = $this->transportRouteService->findById($id);

        return $this->successResponse(new TransportRouteResource($route), 'Transport route retrieved successfully');
    }

    /**
     * Update the specified transport route.
     *
     * @param  TransportRouteUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(TransportRouteUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $route = $this->transportRouteService->update($id, $attributes);

        return $this->successResponse(new TransportRouteResource($route), 'Transport route updated successfully');
    }

    /**
     * Update popular positions for multiple transport routes.
     *
     * @param  TransportRouteUpdatePopularPositionsRequest  $request
     * @return JsonResponse
     */
    public function updatePopularPositions(TransportRouteUpdatePopularPositionsRequest $request): JsonResponse
    {
        $this->transportRouteService->updatePopularPositions($request->validated('popular_positions'));

        return $this->successResponse([], 'Popular transport route positions updated successfully');
    }

    /**
     * Remove the specified transport route.
     *
     * @param  TransportRouteDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(TransportRouteDestroyRequest $request, int $id): JsonResponse
    {
        $this->transportRouteService->destroy($id);

        return $this->successResponse([], 'Transport route deleted successfully');
    }

    /**
     * Display all active counters for the specified transport route.
     *
     * @param  TransportRouteShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function routeWiseCounters(TransportRouteShowRequest $request, int $id): JsonResponse
    {
        $counters = $this->transportRouteService->routeWiseCounters($id);

        return $this->successResponse($counters, 'Transport route wise counters retrieved successfully');
    }
}
