<?php

namespace App\Http\Controllers\Report;

use App\Helpers\TripHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\District;
use App\Models\TripInstance;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoachReportController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the coach trips.
     *
     * @param  Request  $request
     * @return void
     */
    public function coach_sales_report(Request $request)
    {
        try {

            // Basic validation only
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1|max:1000',
                'page' => 'nullable|integer|min:1',
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d',
                'coach_id' => 'nullable|exists:coaches,id',
                'coach_type' => 'nullable|in:1,2',
                'bus_id' => 'nullable|exists:buses,id',
                'schedule_id' => 'nullable|exists:schedules,id',
                'seat_plan_id' => 'nullable|exists:seat_plans,id',
                'route_id' => 'nullable|exists:transport_routes,id',
                'driver_id' => 'nullable|integer',
                'supervisor_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Determine query strategy based on parameters
            $perPage = min((int) $request->get('per_page', 15), 1000); // Cap at 1000
            $page = max((int) $request->get('page', 1), 1); // Minimum page 1

            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            $rawQuery = Booking::with([
                'customer',
                'boarding',
                'dropping',
                'route',
                'bookingDetails.seat',
            ])
                ->where('date', '>=', $startDate->format('Y-m-d'))
                ->where('date', '<=', $endDate->format('Y-m-d'));

            if ($request->filled('route_id')) {
                $rawQuery->where('route_id', $request->route_id);
            }

            $rawQuery->orderBy('id', 'desc');

            $paginatedData = $rawQuery->paginate($perPage, ['*'], 'page', $page);

            foreach ($paginatedData->items() as $booking) {

                if ($booking->route_id != null && $booking->route != null) {
                    $startDistrict = District::where('id', $booking->route->start_id)->first();
                    $endDistrict = District::where('id', $booking->route->end_id)->first();
                    $booking->route->route_display = $startDistrict->name.' - '.$endDistrict->name;
                }

            }

            return $this->successResponse($paginatedData, 'Coach sales retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve coach sales: '.$e->getMessage(), 500);
        }

        return $this->success($request->all());

    }

    /**
     * Display a listing of the coach trips.
     *
     * @param  Request  $request
     * @return void
     */
    public function coach_trips_report(Request $request)
    {
        try {
            // Basic validation only
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1|max:1000',
                'page' => 'nullable|integer|min:1',
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d',
                'coach_id' => 'nullable|exists:coaches,id',
                'coach_type' => 'nullable|in:1,2',
                'bus_id' => 'nullable|exists:buses,id',
                'schedule_id' => 'nullable|exists:schedules,id',
                'seat_plan_id' => 'nullable|exists:seat_plans,id',
                'route_id' => 'nullable|exists:transport_routes,id',
                'driver_id' => 'nullable|integer',
                'supervisor_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Determine query strategy based on parameters
            $perPage = min((int) $request->get('per_page', 15), 1000); // Cap at 1000
            $page = max((int) $request->get('page', 1), 1); // Minimum page 1

            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            /**
             * Query across multiple partitions for date range (auto-creates if needed)
             */

            // This will auto-create partitions for the date range
            $tripInstance = new TripInstance;
            $rawQuery = $tripInstance->queryAcrossPartitions($startDate, $endDate);

            if ($request->filled('coach_type')) {
                $rawQuery->where('coach_type', $request->coach_type);
            }

            if ($request->filled('coach_id')) {
                $rawQuery->where('coach_id', $request->coach_id);
            }

            if ($request->filled('bus_id')) {
                $rawQuery->where('bus_id', $request->bus_id);
            }

            if ($request->filled('schedule_id')) {
                $rawQuery->where('schedule_id', $request->schedule_id);
            }

            if ($request->filled('route_id')) {
                $rawQuery->where('route_id', $request->route_id);
            }

            if ($request->filled('driver_id')) {
                $rawQuery->where('driver_id', $request->driver_id);
            }

            if ($request->filled('supervisor_id')) {
                $rawQuery->where('supervisor_id', $request->supervisor_id);
            }

            $rawQuery->orderBy('id', 'desc');

            // Get results and convert to collection
            $rawResults = $rawQuery->get();
            $tripInstances = $rawResults->map(function ($item) {
                $model = new TripInstance;
                $model->setRawAttributes((array) $item, true);

                return $model;
            });

            // Load relationships for cross-partition results
            $tripInstanceIds = $tripInstances->pluck('id')->toArray();
            $relatedData = $this->loadRelatedDataForIds($tripInstanceIds);

            // Transform cross-partition data
            $transformedTrips = $tripInstances->map(function ($trip) use ($relatedData) {
                return $this->transformTripData($trip, $relatedData);
            });

            // Manual pagination for cross-partition results
            $total = $transformedTrips->count();
            $items = $transformedTrips->forPage($page, $perPage)->values();

            $paginatedData = [
                'current_page' => (int) $page,
                'data' => $items,
                'first_page_url' => request()->url().'?page=1',
                'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
                'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                'last_page_url' => request()->url().'?page='.($total > 0 ? ceil($total / $perPage) : 1),
                'next_page_url' => $page < ceil($total / $perPage) ? request()->url().'?page='.($page + 1) : null,
                'path' => request()->url(),
                'per_page' => $perPage,
                'prev_page_url' => $page > 1 ? request()->url().'?page='.($page - 1) : null,
                'to' => min($page * $perPage, $total),
                'total' => $total,
            ];

            return $this->successResponse($paginatedData, 'Coach trip instances retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve trip instances: '.$e->getMessage(), 500);
        }

        return $this->success($request->all());

    }

    /**
     * Transform trip data with comprehensive information
     */
    private function transformTripData($trip, $relatedData = null)
    {
        // Get district names
        $startDistrict = null;
        $endDistrict = null;

        if ($trip->route) {
            $startDistrict = \DB::table('districts')->where('id', $trip->route->start_id)->first();
            $endDistrict = \DB::table('districts')->where('id', $trip->route->end_id)->first();
        } elseif ($relatedData && isset($relatedData['routes'][$trip->route_id])) {
            $route = $relatedData['routes'][$trip->route_id];
            $startDistrict = \DB::table('districts')->where('id', $route->start_id)->first();
            $endDistrict = \DB::table('districts')->where('id', $route->end_id)->first();
        }

        return [
            // Basic trip information
            'id' => $trip->id,
            'trip_id' => $trip->id,
            'trip_date' => $trip->trip_date instanceof Carbon ? $trip->trip_date->format('Y-m-d') : $trip->trip_date,
            'trip_date_formatted' => $trip->trip_date instanceof Carbon ? $trip->trip_date->format('l, F j, Y') : Carbon::parse($trip->trip_date)->format('l, F j, Y'),
            'status' => $trip->status,
            'status_name' => $this->getStatusName($trip->status),
            'coach_type' => $trip->coach_type,
            'coach_type_name' => $this->getCoachTypeName($trip->coach_type),
            'migrated_trip_id' => $trip->migrated_trip_id,

            // Coach details
            'coach_id' => $trip->coach_id,
            'coach' => [
                'id' => $trip->coach->id ?? ($relatedData['coaches'][$trip->coach_id]->id ?? null),
                'coach_no' => $trip->coach->coach_no ?? ($relatedData['coaches'][$trip->coach_id]->coach_no ?? null),
                'seat_plan_id' => $trip->coach->seat_plan_id ?? ($relatedData['coaches'][$trip->coach_id]->seat_plan_id ?? null),
                'coach_type' => $trip->coach->coach_type ?? ($relatedData['coaches'][$trip->coach_id]->coach_type ?? null),
                'coach_type_name' => $trip->coach ? $this->getCoachTypeName($trip->coach->coach_type) :
                ($relatedData['coaches'][$trip->coach_id] ? $this->getCoachTypeName($relatedData['coaches'][$trip->coach_id]->coach_type) : null),
                'status' => $trip->coach->status ?? ($relatedData['coaches'][$trip->coach_id]->status ?? null),
            ],

            // Bus details
            'bus_id' => $trip->bus_id,
            'bus' => [
                'id' => $trip->bus->id ?? ($relatedData['buses'][$trip->bus_id]->id ?? null),
                'registration_number' => $trip->bus->registration_number ?? ($relatedData['buses'][$trip->bus_id]->registration_number ?? null),
                'manufacturer_company' => $trip->bus->manufacturer_company ?? ($relatedData['buses'][$trip->bus_id]->manufacturer_company ?? null),
                'model_year' => $trip->bus->model_year ?? ($relatedData['buses'][$trip->bus_id]->model_year ?? null),
                'color' => $trip->bus->color ?? ($relatedData['buses'][$trip->bus_id]->color ?? null),
                'status' => $trip->bus->status ?? ($relatedData['buses'][$trip->bus_id]->status ?? null),
            ],

            // Schedule details
            'schedule_id' => $trip->schedule_id,
            'schedule' => [
                'id' => $trip->schedule->id ?? ($relatedData['schedules'][$trip->schedule_id]->id ?? null),
                'name' => $trip->schedule->name ?? ($relatedData['schedules'][$trip->schedule_id]->name ?? null),
                'status' => $trip->schedule->status ?? ($relatedData['schedules'][$trip->schedule_id]->status ?? null),
            ],

            // Route details
            'route_id' => $trip->route_id,
            'route' => [
                'id' => $trip->route->id ?? ($relatedData['routes'][$trip->route_id]->id ?? null),
                'start_id' => $trip->route->start_id ?? ($relatedData['routes'][$trip->route_id]->start_id ?? null),
                'end_id' => $trip->route->end_id ?? ($relatedData['routes'][$trip->route_id]->end_id ?? null),
                'distance' => $trip->route->distance ?? ($relatedData['routes'][$trip->route_id]->distance ?? null),
                'duration' => $trip->route->duration ?? ($relatedData['routes'][$trip->route_id]->duration ?? null),
                'status' => $trip->route->status ?? ($relatedData['routes'][$trip->route_id]->status ?? null),
                'start_district' => [
                    'id' => $startDistrict->id ?? null,
                    'name' => $startDistrict->name ?? 'Unknown',
                    'code' => $startDistrict->code ?? null,
                ],
                'end_district' => [
                    'id' => $endDistrict->id ?? null,
                    'name' => $endDistrict->name ?? 'Unknown',
                    'code' => $endDistrict->code ?? null,
                ],
                'route_display' => sprintf('%s → %s',
                    $startDistrict->name ?? 'Unknown',
                    $endDistrict->name ?? 'Unknown'
                ),
            ],

            // Driver details
            'driver_id' => $trip->driver_id,
            'driver' => [
                'id' => $trip->driver->id ?? ($relatedData['employees'][$trip->driver_id]->id ?? null),
                'name' => $trip->driver->name ?? ($relatedData['employees'][$trip->driver_id]->name ?? null),
                'contact_no' => $trip->driver->contact_no ?? ($relatedData['employees'][$trip->driver_id]->contact_no ?? null),
                'email' => $trip->driver->email ?? ($relatedData['employees'][$trip->driver_id]->email ?? null),
                'license_no' => $trip->driver->license_no ?? ($relatedData['employees'][$trip->driver_id]->license_no ?? null),
                'license_expired_date' => $trip->driver->license_expired_date ?? ($relatedData['employees'][$trip->driver_id]->license_expired_date ?? null),
                'status' => $trip->driver->status ?? ($relatedData['employees'][$trip->driver_id]->status ?? null),
            ],

            // Supervisor details
            'supervisor_id' => $trip->supervisor_id,
            'supervisor' => [
                'id' => $trip->supervisor->id ?? ($relatedData['employees'][$trip->supervisor_id]->id ?? null),
                'name' => $trip->supervisor->name ?? ($relatedData['employees'][$trip->supervisor_id]->name ?? null),
                'contact_no' => $trip->supervisor->contact_no ?? ($relatedData['employees'][$trip->supervisor_id]->contact_no ?? null),
                'email' => $trip->supervisor->email ?? ($relatedData['employees'][$trip->supervisor_id]->email ?? null),
                'status' => $trip->supervisor->status ?? ($relatedData['employees'][$trip->supervisor_id]->status ?? null),
            ],

            // Seat plan details
            'seat_plan_id' => $trip->seat_plan_id,
            'seat_plan' => [
                'id' => $trip->seatPlan->id ?? ($relatedData['seat_plans'][$trip->seat_plan_id]->id ?? null),
                'name' => $trip->seatPlan->name ?? ($relatedData['seat_plans'][$trip->seat_plan_id]->name ?? null),
                'floor' => $trip->seatPlan->floor ?? ($relatedData['seat_plans'][$trip->seat_plan_id]->floor ?? null),
                'rows' => $trip->seatPlan->rows ?? ($relatedData['seat_plans'][$trip->seat_plan_id]->rows ?? null),
                'cols' => $trip->seatPlan->cols ?? ($relatedData['seat_plans'][$trip->seat_plan_id]->cols ?? null),
                'layout_type' => $trip->seatPlan->layout_type ?? ($relatedData['seat_plans'][$trip->seat_plan_id]->layout_type ?? null),
            ],

            // Calculate seat information
            'total_seats' => $this->getTotalSeats($trip->seat_plan_id),
            'seat_inventory_summary' => $this->getSeatInventorySummary($trip),

            // Model state checks
            'is_ac' => $trip->coach_type == 1,
            'is_active' => $trip->status == 1,
            'is_migrated' => $trip->status == 2,

            // Migrated trip info
            'migrated_trip' => $trip->migratedTrip ? [
                'id' => $trip->migratedTrip->id,
                'trip_date' => $trip->migratedTrip->trip_date,
                'status' => $trip->migratedTrip->status,
            ] : null,

            // Audit fields
            'created_by' => $trip->created_by,
            'updated_by' => $trip->updated_by,
            'migrated_by' => $trip->migrated_by,
            'created_at' => $trip->created_at,
            'updated_at' => $trip->updated_at,
        ];
    }

    private function getTotalSeats($seatPlanId)
    {
        return TripHelper::getTotalSeats($seatPlanId);
    }

    private function getSeatInventorySummary($trip)
    {
        return TripHelper::getSeatInventorySummary($trip);
    }

    private function getStatusName($status)
    {
        return TripHelper::getStatusName($status);
    }

    private function getCoachTypeName($coach_type)
    {
        return TripHelper::getCoachTypeName($coach_type);
    }

    /**
     * Load related data for cross-partition results
     */
    private function loadRelatedDataForIds($tripInstanceIds)
    {

        if (empty($tripInstanceIds)) {
            return [];
        }

        // Get all foreign keys from trip instances
        $tripData = \DB::table('trip_instances_'.now()->format('Ym'))
            ->whereIn('id', $tripInstanceIds)
            ->get(['coach_id', 'bus_id', 'schedule_id', 'seat_plan_id', 'route_id', 'driver_id', 'supervisor_id'])
            ->toArray();

        $coachIds = array_unique(array_filter(array_column($tripData, 'coach_id')));
        $busIds = array_unique(array_filter(array_column($tripData, 'bus_id')));
        $scheduleIds = array_unique(array_filter(array_column($tripData, 'schedule_id')));
        $seatPlanIds = array_unique(array_filter(array_column($tripData, 'seat_plan_id')));
        $routeIds = array_unique(array_filter(array_column($tripData, 'route_id')));
        $employeeIds = array_unique(array_filter(array_merge(
            array_column($tripData, 'driver_id'),
            array_column($tripData, 'supervisor_id')
        )));

        return [
            'coaches' => \DB::table('coaches')->whereIn('id', $coachIds)->get()->keyBy('id'),
            'buses' => \DB::table('buses')->whereIn('id', $busIds)->get()->keyBy('id'),
            'schedules' => \DB::table('schedules')->whereIn('id', $scheduleIds)->get()->keyBy('id'),
            'seat_plans' => \DB::table('seat_plans')->whereIn('id', $seatPlanIds)->get()->keyBy('id'),
            'routes' => \DB::table('transport_routes')->whereIn('id', $routeIds)->get()->keyBy('id'),
            'employees' => \DB::table('employees')->whereIn('id', $employeeIds)->get()->keyBy('id'),
        ];
    }
}
