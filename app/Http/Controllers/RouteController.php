<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Route\RouteDestroyRequest;
use App\Http\Requests\Api\Route\RouteIndexRequest;
use App\Http\Requests\Api\Route\RouteShowRequest;
use App\Http\Requests\Api\Route\RouteStoreRequest;
use App\Http\Requests\Api\Route\RouteUpdatePopularPositionsRequest;
use App\Http\Requests\Api\Route\RouteUpdateRequest;
use App\Http\Resources\RouteResource;
use App\Services\RouteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RouteController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RouteService $routeService) {}

    /**
     * Display a paginated listing of routes.
     *
     * @param  RouteIndexRequest  $request
     * @return JsonResponse
     */
    public function index(RouteIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $routes = $this->routeService->pagination($attributes);

        return $this->successResponse(
            RouteResource::collection($routes)->resolve(),
            'Routes retrieved successfully',
            $this->preparePaginator($routes)
        );
    }

    /**
     * Display all popular routes ordered by position.
     *
     * @return JsonResponse
     */
    public function allPopularRoutes(): JsonResponse
    {
        $routes = $this->routeService->allPopular();

        return $this->successResponse(
            RouteResource::collection($routes)->resolve(),
            'Popular routes retrieved successfully'
        );
    }

    /**
     * Store a newly created route.
     *
     * @param  RouteStoreRequest  $request
     * @return JsonResponse
     */
    public function store(RouteStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $route = $this->routeService->store($attributes);

        return $this->createdResponse(new RouteResource($route), 'Route created successfully');
    }

    /**
     * Display the specified route.
     *
     * @param  RouteShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(RouteShowRequest $request, int $id): JsonResponse
    {
        $route = $this->routeService->findById($id);

        return $this->successResponse(new RouteResource($route), 'Route retrieved successfully');
    }

    /**
     * Update the specified route.
     *
     * @param  RouteUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(RouteUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $route = $this->routeService->update($id, $attributes);

        return $this->successResponse(new RouteResource($route), 'Route updated successfully');
    }

    /**
     * Update popular positions for multiple routes.
     *
     * @param  RouteUpdatePopularPositionsRequest  $request
     * @return JsonResponse
     */
    public function updatePopularPositions(RouteUpdatePopularPositionsRequest $request): JsonResponse
    {
        $this->routeService->updatePopularPositions($request->validated('popular_positions'));

        return $this->successResponse([], 'Popular route positions updated successfully');
    }

    /**
     * Remove the specified route.
     *
     * @param  RouteDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(RouteDestroyRequest $request, int $id): JsonResponse
    {
        $this->routeService->destroy($id);

        return $this->successResponse([], 'Route deleted successfully');
    }

    /**
     * Display all active counters for the specified route.
     *
     * @param  RouteShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function routeWiseCounters(RouteShowRequest $request, int $id): JsonResponse
    {
        $counters = $this->routeService->routeWiseCounters($id);

        return $this->successResponse($counters, 'Route wise counters retrieved successfully');
    }
}
