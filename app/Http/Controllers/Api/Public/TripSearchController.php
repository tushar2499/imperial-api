<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\TripInstance\SearchTripsRequest;
use App\Services\TripSearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TripSearchController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TripSearchService $tripSearchService) {}

    public function searchTrips(SearchTripsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->tripSearchService->search($validated);

            return $this->successResponse([
                'trips' => $result['trips'],
                'search_criteria' => [
                    'trip_date' => $validated['trip_date'],
                    'route_start_id' => (int) $validated['route_start_id'],
                    'route_end_id' => (int) $validated['route_end_id'],
                    'coach_no' => $validated['coach_no'] ?? null,
                    'schedule_id' => isset($validated['schedule_id']) ? (int) $validated['schedule_id'] : null,
                ],
                'total_trips' => $result['trips']->total(),
                'boarding_counters' => $result['boardingCounters'],
                'dropping_counters' => $result['droppingCounters'],
            ], 'Active trips retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search trips: '.$e->getMessage(), 500);
        }
    }
}
