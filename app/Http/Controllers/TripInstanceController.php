<?php

namespace App\Http\Controllers;

use App\Models\TripBoardingDropping;
use App\Models\TripInstance;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\TripHelper;
use App\Models\SeatInventory;

class TripInstanceController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of trip instances
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Determine query strategy based on parameters
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : null;
            $endDate   = $request->filled('end_date') ? Carbon::parse($request->end_date) : null;
            $tripDate  = $request->filled('trip_date') ? Carbon::parse($request->trip_date) : null;

            /**
             * Query specific date partition (auto-creates if needed)
             */
            if ($tripDate) {
                $query = TripInstance::forDate($tripDate);
            }

            /**
             * Query across multiple partitions for date range (auto-creates if needed)
             */
            elseif ($startDate && $endDate) {
                // This will auto-create partitions for the date range
                $tripInstance = new TripInstance();
                $rawQuery     = $tripInstance->queryAcrossPartitions($startDate, $endDate);

                if ($request->filled('status')) {
                    $rawQuery->where('status', $request->status);
                }

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

                // Sorting
                $sortBy    = $request->get('sort_by', 'trip_date');
                $sortOrder = $request->get('sort_order', 'desc');
                $rawQuery->orderBy($sortBy, $sortOrder);

                // Get results and convert to collection
                $rawResults    = $rawQuery->get();
                $tripInstances = $rawResults->map(function ($item) {
                    $model = new TripInstance();
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
                $page    = $request->get('page', 1);
                $perPage = $request->get('per_page', 15);
                $total   = $transformedTrips->count();
                $items   = $transformedTrips->forPage($page, $perPage)->values();

                $paginatedData = [
                    'current_page'   => (int) $page,
                    'data'           => $items,
                    'first_page_url' => request()->url() . '?page=1',
                    'from'           => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
                    'last_page'      => $total > 0 ? ceil($total / $perPage) : 1,
                    'last_page_url'  => request()->url() . '?page=' . ($total > 0 ? ceil($total / $perPage) : 1),
                    'next_page_url'  => $page < ceil($total / $perPage) ? request()->url() . '?page=' . ($page + 1) : null,
                    'path'           => request()->url(),
                    'per_page'       => $perPage,
                    'prev_page_url'  => $page > 1 ? request()->url() . '?page=' . ($page - 1) : null,
                    'to'             => min($page * $perPage, $total),
                    'total'          => $total,
                ];

                return $this->successResponse($paginatedData, 'Trip instances retrieved successfully');
            }

            /**
             * Default to current month partition (auto-creates if needed)
             */
            else {
                $query = TripInstance::forCurrentMonth();
            }

            // Add relationships
            $query->with([
                'coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'migratedTrip',
            ]);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('coach_type')) {
                $query->where('coach_type', $request->coach_type);
            }

            if ($request->filled('coach_id')) {
                $query->where('coach_id', $request->coach_id);
            }

            if ($request->filled('bus_id')) {
                $query->where('bus_id', $request->bus_id);
            }

            if ($request->filled('schedule_id')) {
                $query->where('schedule_id', $request->schedule_id);
            }

            if ($request->filled('route_id')) {
                $query->where('route_id', $request->route_id);
            }

            if ($request->filled('driver_id')) {
                $query->where('driver_id', $request->driver_id);
            }

            if ($request->filled('supervisor_id')) {
                $query->where('supervisor_id', $request->supervisor_id);
            }

            if ($request->filled('today') && $request->today) {
                $query->today();
            }

            if ($request->filled('upcoming') && $request->upcoming) {
                $query->upcoming();
            }

            if ($request->filled('past') && $request->past) {
                $query->past();
            }

            // Sorting
            $sortBy    = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage       = $request->get('per_page', 15);
            $tripInstances = $query->paginate($perPage);

            // Transform the data with comprehensive information
            $transformedTrips = $tripInstances->getCollection()->map(function ($trip) {
                return $this->transformTripData($trip);
            });

            // Update the collection in paginated result
            $tripInstances->setCollection($transformedTrips);

            return $this->successResponse($tripInstances, 'Trip instances retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve trip instances: ' . $e->getMessage(), 500);
        }
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

    /**
     * Load related data for cross-partition results
     */
    private function loadRelatedDataForIds($tripInstanceIds)
    {
        if (empty($tripInstanceIds)) {
            return [];
        }

        // Get all foreign keys from trip instances
        $tripData = \DB::table('trip_instances_' . now()->format('Ym'))
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
            'routes' => \DB::table('routes')->whereIn('id', $routeIds)->get()->keyBy('id'),
            'employees' => \DB::table('employees')->whereIn('id', $employeeIds)->get()->keyBy('id'),
        ];
    }

    /**
     * Store a newly created trip instance
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Basic validation only
            $validator = Validator::make($request->all(), [
                'coach_id'                                         => 'required|exists:coaches,id',
                'bus_id'                                           => 'required|exists:buses,id',
                'schedule_id'                                      => 'required|exists:schedules,id',
                'seat_plan_id'                                     => 'required|exists:seat_plans,id',
                'route_id'                                         => 'required|exists:routes,id',
                'coach_type'                                       => 'required|in:1,2',
                'trip_date'                                        => 'required|date',
                'driver_id'                                        => 'nullable|integer',
                'supervisor_id'                                    => 'nullable|integer',
                'status'                                           => 'sometimes|in:0,1,2',
                'migrated_trip_id'                                 => 'nullable|integer',
                'auto_create_seat_inventory'                       => 'sometimes|boolean',// Optional flag,

                // Boarding/Dropping points validation
                'boarding_dropping_points'                         => 'required|array|min:1',
                'boarding_dropping_points.*.counter_id'            => 'required|exists:counters,id',
                'boarding_dropping_points.*.type'                  => 'required|in:1,2',
                'boarding_dropping_points.*.time'                  => 'required|date_format:H:i',
                'boarding_dropping_points.*.starting_point_status' => 'sometimes|boolean',
                'boarding_dropping_points.*.ending_point_status'   => 'sometimes|boolean',
                'boarding_dropping_points.*.status'                => 'sometimes|in:0,1',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Pre-create partitions before starting transaction to avoid DDL in transactions
            $tripDate = $request->input('trip_date');

            // Create TripInstance partition
            $tempTripInstance     = new TripInstance();
            $tripPartitionCreated = $tempTripInstance->ensurePartitionExists($tripDate);

            if (!$tripPartitionCreated) {
                return $this->errorResponse('Failed to create trip partition for trip date', 500);
            }

            // Create SeatInventory partition
            $tempSeatInventory    = new \App\Models\SeatInventory();
            $seatPartitionCreated = $tempSeatInventory->ensurePartitionExists($tripDate);

            if (!$seatPartitionCreated) {
                return $this->errorResponse('Failed to create seat inventory partition for trip date', 500);
            }

            DB::beginTransaction();

            // Create trip instance with auto-partitioning
            $tripInstance = TripInstance::create([
                'coach_id'         => $request->input('coach_id'),
                'bus_id'           => $request->input('bus_id'),
                'schedule_id'      => $request->input('schedule_id'),
                'seat_plan_id'     => $request->input('seat_plan_id'),
                'route_id'         => $request->input('route_id'),
                'coach_type'       => $request->input('coach_type'),
                'driver_id'        => $request->input('driver_id'),
                'supervisor_id'    => $request->input('supervisor_id'),
                'trip_date'        => $tripDate,
                'status'           => $request->input('status', 1),
                'migrated_trip_id' => $request->input('migrated_trip_id'),
                'created_by'       => auth()->check() ? auth()->user()->id : null,
            ]);

            // Auto-create seat inventory if requested (default true)
            $autoCreateSeats     = $request->input('auto_create_seat_inventory', true);
            $seatInventoryResult = null;

            if ($autoCreateSeats) {
                // Create seat inventory using the service
                $seatInventoryService = new \App\Services\SeatInventoryService();
                $seatInventoryResult  = $seatInventoryService->createSeatInventoryForTrip(
                    $tripInstance->id,
                    $tripInstance->seat_plan_id
                );

                if (!$seatInventoryResult['success']) {
                    // If seat inventory creation fails, rollback everything
                    throw new \Exception('Failed to create seat inventory: ' . $seatInventoryResult['message']);
                }

            }

            foreach ($request->input('boarding_dropping_points') as $point) {
                TripBoardingDropping::create([
                    'trip_id'               => $tripInstance->id,
                    'counter_id'            => $point['counter_id'],
                    'type'                  => $point['type'],
                    'time'                  => $point['time'],
                    'starting_point_status' => $point['starting_point_status'] ?? 0,
                    'ending_point_status'   => $point['ending_point_status'] ?? 0,
                    'status'                => $point['status'] ?? 1,
                    'created_by'            => auth()->user()->id,
                ]);
            }

            DB::commit();

            // Prepare response data
            $responseData = [
                'trip_instance'          => $tripInstance,
                'seat_inventory_created' => $autoCreateSeats,
            ];

            if ($seatInventoryResult && $seatInventoryResult['success']) {
                $responseData['seat_inventory'] = $seatInventoryResult['data'];
            }

            return $this->successResponse([
                'data'    => $responseData,
                'message' => 'Trip instance created successfully' . ($autoCreateSeats ? ' with seat inventory' : ''),
            ], 'Trip instance created successfully', 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create trip instance',
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }

    }

    /**
     * Display the specified trip instance
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        try {
            // Search across all partitions (auto-creates if needed during search)
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            // Load relationships (including fare)
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan.floors', 'route',
                'driver', 'supervisor', 'migratedTrip', 'creator', 'updater', 'migrator',
                'boardingDroppings.counter',
                'fare' // Add fare relationship
            ]);

            // Always load seat inventory with seat details
            $seatInventoryData = [];
            $autoCreated       = false;

            try {
                // First, ensure the seat inventory partition exists for this trip's date
                $seatInventoryModel = new \App\Models\SeatInventory();
                $partitionCreated   = $seatInventoryModel->ensurePartitionExists($tripInstance->trip_date);

                if (!$partitionCreated) {
                    \Log::warning("Could not create seat inventory partition for trip {$id}");
                }

                // Get seat inventory for this trip with seat details
                $seatInventories = \App\Models\SeatInventory::forTrip($id)
                    ->with(['seat' => function ($query) {
                        $query->select('id', 'seat_plan_floor_id', 'seat_plan_id', 'seat_number', 'row_position', 'col_position', 'seat_type');
                    },
                    ])
                    ->get(['id', 'seat_id', 'booking_status', 'blocked_until', 'booking_id', 'last_locked_user_id']);

                \Log::info("Found " . $seatInventories->count() . " seat inventories for trip {$id}");

                if ($seatInventories->isEmpty()) {
                    // If no seat inventory exists, try to create it automatically
                    \Log::info("No seat inventory found for trip {$id}, attempting to create it automatically");

                    $seatInventoryService = new \App\Services\SeatInventoryService();
                    $createResult         = $seatInventoryService->createSeatInventoryForTrip($id, $tripInstance->seat_plan_id);

                    if ($createResult['success']) {
                        $autoCreated = true;
                        \Log::info("Successfully auto-created seat inventory for trip {$id}");

                        // Re-fetch the seat inventory after creation
                        $seatInventories = \App\Models\SeatInventory::forTrip($id)
                            ->with(['seat' => function ($query) {
                                $query->select('id', 'seat_plan_floor_id', 'seat_plan_id', 'seat_number', 'row_position', 'col_position', 'seat_type');
                            },
                            ])
                            ->get(['id', 'seat_id', 'booking_status', 'blocked_until', 'booking_id', 'last_locked_user_id']);

                        \Log::info("After creation, found " . $seatInventories->count() . " seat inventories for trip {$id}");
                    } else {
                        \Log::error("Failed to auto-create seat inventory for trip {$id}: " . $createResult['message']);
                    }
                }

                // Transform seat inventory data to match your desired structure
                $seatInventoryData = $seatInventories->map(function ($inventory) {
                    return [
                        'id'                  => $inventory->id,
                        'seat_id'             => $inventory->seat_id,
                        'booking_status'      => $inventory->booking_status,
                        'blocked_until'       => $inventory->blocked_until,
                        'booking_id'          => $inventory->booking_id,
                        'last_locked_user_id' => $inventory->last_locked_user_id,
                        'seat_plan_floor_id'  => $inventory->seat->seat_plan_floor_id ?? null,
                        'seat_number'         => $inventory->seat->seat_number ?? null,
                        'row_position'        => $inventory->seat->row_position ?? null,
                        'col_position'        => $inventory->seat->col_position ?? null,
                        'seat_type'           => $inventory->seat->seat_type ?? null,
                    ];
                })->toArray();

            } catch (\Exception $e) {
                \Log::error("Failed to load seat inventory for trip {$id}: " . $e->getMessage() . " in " . $e->getFile() . " at line " . $e->getLine());
                $seatInventoryData = [];
            }

            // Prepare fare information
            $fareInfo = null;
            if ($tripInstance->fare) {
                $fareInfo = [
                    'fare_id' => $tripInstance->fare->id,
                    'amount' => $tripInstance->fare->amount ?? null, // Adjust field name as per your fare table
                    'coach_type' => $tripInstance->fare->coach_type_name,
                    'route_id' => $tripInstance->fare->route_id,
                    'seat_plan_id' => $tripInstance->fare->seat_plan_id,
                    'status' => $tripInstance->fare->status_name,
                    'from_date' => $tripInstance->fare->from_date ? $tripInstance->fare->from_date->format('Y-m-d H:i:s') : null,
                    'to_date' => $tripInstance->fare->to_date ? $tripInstance->fare->to_date->format('Y-m-d H:i:s') : null,
                    'created_by' => $tripInstance->fare->created_by,
                    'updated_by' => $tripInstance->fare->updated_by,
                    'created_at' => $tripInstance->fare->created_at,
                    'updated_at' => $tripInstance->fare->updated_at,
                ];
            }

            // Convert trip instance to array and add seat inventory and fare info
            $tripInstanceArray = $tripInstance->toArray();
            $tripInstanceArray['seat_inventory'] = $seatInventoryData;
            $tripInstanceArray['fare_info'] = $fareInfo;

            // Prepare response data
            $responseData = [
                'trip_instance'  => $tripInstanceArray,
                'partition_info' => [
                    'current_table'               => $tripInstance->getTable(),
                    'trip_date'                   => $tripInstance->trip_date->format('Y-m-d'),
                    'partition_month'             => $tripInstance->trip_date->format('Y-m'),
                    'seat_inventory_auto_created' => $autoCreated,
                    'seat_inventory_count'        => count($seatInventoryData),
                ],
            ];

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Trip instance retrieved successfully',
                'data'    => $responseData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 500,
                'message' => 'Failed to retrieve trip instance: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Update the specified trip instance
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'coach_id'                      => 'sometimes|exists:coaches,id',
            'bus_id'                        => 'sometimes|exists:buses,id',
            'schedule_id'                   => 'sometimes|exists:schedules,id',
            'seat_plan_id'                  => 'sometimes|exists:seat_plans,id',
            'route_id'                      => 'sometimes|exists:routes,id',
            'coach_type'                    => 'sometimes|in:1,2',
            'driver_id'                     => 'nullable|exists:employees,id',
            'supervisor_id'                 => 'nullable|exists:employees,id',
            'trip_date'                     => 'sometimes|date',
            'status'                        => 'sometimes|in:0,1,2',
            'migrated_trip_id'              => 'nullable|integer',
            'auto_create_seat_inventory'    => 'sometimes|boolean',// Optional flag,

            // Boarding/Dropping points validation
            'boarding_dropping_points'                         => 'required|array|min:1',
            'boarding_dropping_points.*.counter_id'            => 'required|exists:counters,id',
            'boarding_dropping_points.*.type'                  => 'required|in:1,2',
            'boarding_dropping_points.*.time'                  => 'required|date_format:H:i',
            'boarding_dropping_points.*.starting_point_status' => 'sometimes|boolean',
            'boarding_dropping_points.*.ending_point_status'   => 'sometimes|boolean',
            'boarding_dropping_points.*.status'                => 'sometimes|in:0,1',

        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Find trip instance across partitions
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            /**
             * If trip_date is being changed, handle partition migration
             */

            if ($request->filled('trip_date') && $request->input('trip_date') != $tripInstance->trip_date->format('Y-m-d')) {
                $newTripDate           = $request->input('trip_date');
                $newPartitionTable     = (new TripInstance())->getPartitionTableName($newTripDate);
                $currentPartitionTable = $tripInstance->getTable();

                // Check for duplicate in new partition (auto-creates partition)
                $existingTrip = TripInstance::forDate($newTripDate)
                    ->where('coach_id', $request->input('coach_id', $tripInstance->coach_id))
                    ->where('schedule_id', $request->input('schedule_id', $tripInstance->schedule_id))
                    ->whereDate('trip_date', $newTripDate)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingTrip) {
                    return $this->errorResponse('A trip instance already exists for this coach, schedule, and date.', 422);
                }

                /**
                 * If partition changes, create new record and delete old one
                 */

                if ($newPartitionTable !== $currentPartitionTable) {
                    // Prepare data for new record
                    $updateData = $tripInstance->toArray();
                    unset($updateData['id']);

                    foreach ($request->only(['coach_id', 'bus_id', 'schedule_id', 'seat_plan_id', 'route_id', 'coach_type', 'driver_id', 'supervisor_id', 'trip_date', 'status', 'migrated_trip_id']) as $key => $value) {

                        if ($request->filled($key) || $request->has($key)) {
                            $updateData[$key] = $value;
                        }

                    }

                    $updateData['updated_by'] = auth()->user()->id;

                    // Create in new partition (auto-creates partition)
                    $newTripInstance = TripInstance::create($updateData);

                    // Delete from old partition
                    $tripInstance->delete();

                    $tripInstance = $newTripInstance;
                } else {
                    // Same partition, regular update
                    $updateData = [];

                    foreach ($request->only(['coach_id', 'bus_id', 'schedule_id', 'seat_plan_id', 'route_id', 'coach_type', 'driver_id', 'supervisor_id', 'trip_date', 'status', 'migrated_trip_id']) as $key => $value) {

                        if ($request->filled($key) || $request->has($key)) {
                            $updateData[$key] = $value;
                        }

                    }

                    $updateData['updated_by'] = auth()->user()->id;
                    $updateData['updated_at'] = now();

                    $tripInstance->update($updateData);
                }

            } else {
                // Regular update without date change
                $updateData = [];

                foreach ($request->only(['coach_id', 'bus_id', 'schedule_id', 'seat_plan_id', 'route_id', 'coach_type', 'driver_id', 'supervisor_id', 'status', 'migrated_trip_id']) as $key => $value) {

                    if ($request->filled($key) || $request->has($key)) {
                        $updateData[$key] = $value;
                    }

                }

                $updateData['updated_by'] = auth()->user()->id;
                $updateData['updated_at'] = now();

                $tripInstance->update($updateData);
            }

            TripBoardingDropping::where('trip_id', $tripInstance->id)->delete();
            foreach ($request->input('boarding_dropping_points') as $point) {
                TripBoardingDropping::create([
                    'trip_id'               => $tripInstance->id,
                    'counter_id'            => $point['counter_id'],
                    'type'                  => $point['type'],
                    'time'                  => $point['time'],
                    'starting_point_status' => $point['starting_point_status'] ?? 0,
                    'ending_point_status'   => $point['ending_point_status'] ?? 0,
                    'status'                => $point['status'] ?? 1,
                    'created_by'            => auth()->user()->id,
                ]);
            }

            // Refresh and load relationships
            $tripInstance = $tripInstance->fresh();
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'migratedTrip',
            ]);

            DB::commit();

            return $this->successResponse($tripInstance, 'Trip instance updated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update trip instance: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified trip instance
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find trip instance across partitions
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            TripBoardingDropping::where('trip_id', $tripInstance->id)->delete();
            $tripInstance->delete();

            DB::commit();

            return $this->successResponse(null, 'Trip instance deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete trip instance: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Migrate trip instance to another trip
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'migrated_trip_id' => 'required|integer|different:' . $id,
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Find trip instance across partitions
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            if ($tripInstance->isMigrated()) {
                return $this->errorResponse('Trip instance is already migrated', 422);
            }

            $tripInstance->update([
                'status'           => TripInstance::STATUS_MIGRATED,
                'migrated_trip_id' => $request->input('migrated_trip_id'),
                'migrated_by'      => auth()->user()->id,
                'updated_by'       => auth()->user()->id,
                'updated_at'       => now(),
            ]);

            $tripInstance = $tripInstance->refresh();

            DB::commit();

            return $this->successResponse($tripInstance, 'Trip instance migrated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to migrate trip instance: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Toggle the status of a trip instance
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            // Find trip instance across partitions
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            if ($tripInstance->isMigrated()) {
                return $this->errorResponse('Cannot change status of migrated trip instance', 422);
            }

            $newStatus = $tripInstance->status === 1 ? 0 : 1;
            $tripInstance->update([
                'status'     => $newStatus,
                'updated_by' => auth()->user()->id,
                'updated_at' => now(),
            ]);

            $tripInstance = $tripInstance->refresh();

            DB::commit();

            return $this->successResponse($tripInstance, 'Trip instance status updated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update trip instance status: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get trip instances by date (auto-creates partition)
     *
     * @param string $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDate($date)
    {
        try {
            DB::beginTransaction();

            // This will auto-create partition if needed
            $tripInstances = TripInstance::forDate($date)
                ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor'])
                ->byDate($date)
                ->get();

            DB::commit();

            return $this->successResponse([
                'date'          => $date,
                'total_records' => $tripInstances->count(),
                'data'          => $tripInstances,
            ], 'Trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve trip instances: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get today's trip instances (auto-creates partition)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToday()
    {
        try {
            DB::beginTransaction();

            // This will auto-create today's partition if needed
            $tripInstances = TripInstance::forDate(today())
                ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor'])
                ->today()
                ->active()
                ->get();

            DB::commit();

            return $this->successResponse([
                'date'          => today()->format('Y-m-d'),
                'total_records' => $tripInstances->count(),
                'data'          => $tripInstances,
            ], 'Today\'s trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve today\'s trip instances: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get trip instances by date range (cross-partition query)
     *
     * @param string $startDate
     * @param string $endDate
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDateRange($startDate, $endDate, Request $request)
    {
        try {
            DB::beginTransaction();

            $start = Carbon::parse($startDate);
            $end   = Carbon::parse($endDate);

            // Get raw data from multiple partitions (auto-creates partitions)
            $tripInstance = new TripInstance();
            $rawQuery     = $tripInstance->queryAcrossPartitions($start, $end);

            if ($request->filled('status')) {
                $rawQuery->where('status', $request->status);
            }

            if ($request->filled('coach_type')) {
                $rawQuery->where('coach_type', $request->coach_type);
            }

            if ($request->filled('coach_id')) {
                $rawQuery->where('coach_id', $request->coach_id);
            }

            if ($request->filled('route_id')) {
                $rawQuery->where('route_id', $request->route_id);
            }

            // Sorting
            $sortBy    = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $rawQuery->orderBy($sortBy, $sortOrder);

            $tripInstances = $rawQuery->get();

            DB::commit();

            return $this->successResponse([
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'total_records' => $tripInstances->count(),
                'data'          => $tripInstances,
            ], 'Trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve trip instances: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get trip instances from specific partition
     *
     * @param string $yearMonth
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPartition($yearMonth, Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make(['year_month' => $yearMonth], [
                'year_month' => 'required|date_format:Y-m',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $date = Carbon::createFromFormat('Y-m', $yearMonth);

            // This will auto-create partition if needed
            $query = TripInstance::forDate($date)
                ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('coach_id')) {
                $query->where('coach_id', $request->coach_id);
            }

            if ($request->filled('coach_type')) {
                $query->where('coach_type', $request->coach_type);
            }

            if ($request->filled('route_id')) {
                $query->where('route_id', $request->route_id);
            }

            if ($request->filled('driver_id')) {
                $query->where('driver_id', $request->driver_id);
            }

            // Sorting
            $sortBy    = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage       = $request->get('per_page', 15);
            $tripInstances = $query->paginate($perPage);

            DB::commit();

            return $this->successResponse([
                'partition'       => $yearMonth,
                'partition_table' => TripInstance::forDate($date)->getTable(),
                'data'            => $tripInstances,
            ], 'Partition trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve partition trip instances: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get partition statistics and health info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPartitionInfo()
    {
        try {
            $tripInstance  = new TripInstance();
            $allPartitions = $tripInstance->getAllPartitionTables();

            $statistics   = [];
            $totalRecords = 0;
            $totalSizeMB  = 0;

            foreach ($allPartitions as $partition) {
                try {
                    $count = DB::table($partition)->count();
                    $size  = DB::select("
                        SELECT
                            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                        FROM information_schema.tables
                        WHERE table_schema = DATABASE()
                        AND table_name = ?
                    ", [$partition])[0]->size_mb ?? 0;

                    // Extract month from table name
                    $month = str_replace('trip_instances_', '', $partition);

                    if (strlen($month) === 6 && is_numeric($month)) {
                        $formattedMonth = Carbon::createFromFormat('Ym', $month)->format('Y-m');
                    } else {
                        $formattedMonth = 'Unknown';
                    }

                    $statistics[] = [
                        'table'        => $partition,
                        'month'        => $formattedMonth,
                        'record_count' => $count,
                        'size_mb'      => (float) $size,
                    ];

                    $totalRecords += $count;
                    $totalSizeMB += (float) $size;
                } catch (\Exception $e) {
                    // Skip if table query fails
                    continue;
                }

            }

            $currentMonth = now()->format('Ym');
            $nextMonth    = now()->addMonth()->format('Ym');

            $currentPartitionExists = in_array('trip_instances_' . $currentMonth, $allPartitions);
            $nextPartitionExists    = in_array('trip_instances_' . $nextMonth, $allPartitions);

            // Sort statistics by month
            usort($statistics, function ($a, $b) {
                return strcmp($a['month'], $b['month']);
            });

            return $this->successResponse([
                'total_partitions'               => count($allPartitions),
                'total_records'                  => $totalRecords,
                'total_size_mb'                  => round($totalSizeMB, 2),
                'current_month_partition_exists' => $currentPartitionExists,
                'next_month_partition_exists'    => $nextPartitionExists,
                'current_month'                  => now()->format('Y-m'),
                'next_month'                     => now()->addMonth()->format('Y-m'),
                'recommendations'                => $this->getPartitionRecommendations($currentPartitionExists, $nextPartitionExists, count($allPartitions)),
                'partitions'                     => $statistics,
            ], 'Partition information retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve partition information: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get partition recommendations
     *
     * @param bool $currentExists
     * @param bool $nextExists
     * @param int $totalPartitions
     * @return array
     */
    private function getPartitionRecommendations($currentExists, $nextExists, $totalPartitions): array
    {
        $recommendations = [];

        if (!$currentExists) {
            $recommendations[] = [
                'type'    => 'warning',
                'message' => 'Current month partition does not exist. It will be created automatically when needed.',
            ];
        }

        if (!$nextExists) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => 'Next month partition does not exist. Consider creating it in advance for better performance.',
            ];
        }

        if ($totalPartitions > 24) {
            $recommendations[] = [
                'type'    => 'suggestion',
                'message' => 'You have many partitions (' . $totalPartitions . '). Consider archiving partitions older than 2 years.',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'type'    => 'success',
                'message' => 'Partition setup looks healthy!',
            ];
        }

        return $recommendations;
    }

    public function getSeatInventory($id, Request $request)
    {
        try {
            // Validate filters
            $validator = Validator::make($request->all(), [
                'booking_status'       => 'sometimes|in:0,1,2,3',
                'seat_type'            => 'sometimes|string',
                'include_seat_details' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Check if trip exists
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            // Get seat inventory using the service
            $seatInventoryService = new \App\Services\SeatInventoryService();
            $filters              = $request->only(['booking_status', 'seat_type']);
            $result               = $seatInventoryService->getTripSeatInventory($id, $filters);

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 500);
            }

            // Add trip information to response
            $responseData              = $result['data'];
            $responseData['trip_info'] = [
                'id'        => $tripInstance->id,
                'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
                'route'     => $tripInstance->route->name ?? null,
                'coach'     => $tripInstance->coach->name ?? null,
                'status'    => $tripInstance->status_name,
            ];

            return $this->successResponse($responseData, 'Seat inventory retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve seat inventory: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Create seat inventory for a trip (if not exists)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSeatInventory($id)
    {
        try {
            // Check if trip exists
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            // Create seat inventory using the service
            $seatInventoryService = new \App\Services\SeatInventoryService();
            $result               = $seatInventoryService->createSeatInventoryForTrip($id, $tripInstance->seat_plan_id);

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 500);
            }

            return $this->successResponse($result['data'], $result['message'], 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create seat inventory: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Block a seat for a trip
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function blockSeat($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_id' => 'required|integer',
                'minutes' => 'sometimes|integer|min:1|max:60',
                'user_id' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $seatInventoryService = new \App\Services\SeatInventoryService();
            $result               = $seatInventoryService->blockSeat(
                $id,
                $request->input('seat_id'),
                $request->input('minutes', 15),
                $request->input('user_id')
            );

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 422);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to block seat: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Book a seat for a trip
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookSeat($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_id'    => 'required|integer',
                'booking_id' => 'required|integer',
                'user_id'    => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $seatInventoryService = new \App\Services\SeatInventoryService();
            $result               = $seatInventoryService->bookSeat(
                $id,
                $request->input('seat_id'),
                $request->input('booking_id'),
                $request->input('user_id')
            );

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 422);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to book seat: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Release a seat for a trip
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function releaseSeat($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $seatInventoryService = new \App\Services\SeatInventoryService();
            $result               = $seatInventoryService->releaseSeat($id, $request->input('seat_id'));

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 422);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to release seat: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Clean up expired blocks for a trip
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanupExpiredBlocks($id)
    {
        try {
            $seatInventoryService = new \App\Services\SeatInventoryService();
            $result               = $seatInventoryService->cleanupExpiredBlocks($id);

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 500);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to cleanup expired blocks: ' . $e->getMessage(), 500);
        }

    }



    public function searchTrips(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'trip_date' => 'required|date|date_format:Y-m-d',
                'route_start_id' => 'required|integer',
                'route_end_id' => 'required|integer',
                'coach_no' => 'sometimes|string|max:50',
                'schedule_id' => 'sometimes|integer',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $tripDate = Carbon::parse($request->trip_date);
            $routeStartId = $request->route_start_id;
            $routeEndId = $request->route_end_id;

            // Build query using partition-aware model with built-in scopes
            $query = TripInstance::forDate($tripDate)
                ->active() // Use built-in scope for active trips
                ->byDate($tripDate) // Use built-in scope for date filtering
                ->with(['fare', 'boardingDroppings.counter']) // Load fare and boarding/dropping relationships
                ->whereHas('route', function ($routeQuery) use ($routeStartId, $routeEndId) {
                    $routeQuery->where('start_id', $routeStartId)
                            ->where('end_id', $routeEndId);
                });

            // Add optional filters
            if ($request->filled('coach_no')) {
                $query->whereHas('coach', function ($coachQuery) use ($request) {
                    $coachQuery->where('coach_no', $request->coach_no);
                });
            }

            if ($request->filled('schedule_id')) {
                $query->where('schedule_id', $request->schedule_id);
            }

            // Sort by trip_date and created_at
            $query->orderBy('trip_date', 'asc')
                ->orderBy('created_at', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $trips = $query->paginate($perPage);

            // Transform the data using model attributes and methods
            $transformedTrips = $trips->getCollection()->map(function ($trip) {

                // Get district names
                $startDistrict = \DB::table('districts')->where('id', $trip->route->start_id)->first();
                $endDistrict = \DB::table('districts')->where('id', $trip->route->end_id)->first();

                // Get boarding (starting) counter information
                $boardingCounter = null;
                $startingPoint = $trip->boardingDroppings->where('starting_point_status', 1)->first();
                if ($startingPoint && $startingPoint->counter) {
                    $boardingCounter = [
                        'id' => $startingPoint->counter->id,
                        'name' => $startingPoint->counter->name,
                        'location' => $startingPoint->counter->location,
                        'contact' => $startingPoint->counter->contact ?? null,
                        'time' => $startingPoint->time,
                    ];
                }

                // Get dropping (ending) counter information
                $droppingCounter = null;
                $endingPoint = $trip->boardingDroppings->where('ending_point_status', 1)->first();
                if ($endingPoint && $endingPoint->counter) {
                    $droppingCounter = [
                        'id' => $endingPoint->counter->id,
                        'name' => $endingPoint->counter->name,
                        'location' => $endingPoint->counter->location,
                        'contact' => $endingPoint->counter->contact ?? null,
                        'time' => $endingPoint->time,
                    ];
                }

                // Prepare fare information
                $fareInfo = null;
                if ($trip->fare) {
                    $fareInfo = [
                        'fare_id' => $trip->fare->id,
                        'amount' => $trip->fare->amount ?? null,
                        'coach_type' => $trip->fare->coach_type_name,
                        'route_id' => $trip->fare->route_id,
                        'seat_plan_id' => $trip->fare->seat_plan_id,
                        'status' => $trip->fare->status_name,
                        'from_date' => $trip->fare->from_date ? $trip->fare->from_date->format('Y-m-d H:i:s') : null,
                        'to_date' => $trip->fare->to_date ? $trip->fare->to_date->format('Y-m-d H:i:s') : null,
                    ];
                }

                return [
                    'trip_id' => $trip->id,
                    'trip_date' => $trip->formatted_trip_date, // Use model accessor
                    'trip_date_formatted' => $trip->trip_date->format('l, F j, Y'),
                    'status' => $trip->status,
                    'status_name' => $trip->status_name, // Use model accessor
                    'coach_type' => $trip->coach_type,
                    'coach_type_name' => $trip->coach_type_name, // Use model accessor
                    'current_partition' => $trip->current_partition, // Use model accessor

                    // Coach details
                    'coach_id' => $trip->coach->id ?? null,
                    'coach_no' => $trip->coach->coach_no ?? null,
                    'coach_seat_plan_id' => $trip->coach->seat_plan_id ?? null,
                    'coach_status' => $trip->coach->status ?? null,

                    // Bus details
                    'bus_id' => $trip->bus->id ?? null,
                    'bus_registration_number' => $trip->bus->registration_number ?? null,
                    'bus_manufacturer' => $trip->bus->manufacturer_company ?? null,
                    'bus_model_year' => $trip->bus->model_year ?? null,

                    // Schedule details
                    'schedule_id' => $trip->schedule->id ?? null,
                    'schedule_name' => $trip->schedule->name ?? null,

                    // Route details
                    'route_id' => $trip->route->id ?? null,
                    'start_id' => $trip->route->start_id ?? null,
                    'end_id' => $trip->route->end_id ?? null,
                    'distance' => $trip->route->distance ?? null,
                    'duration' => $trip->route->duration ?? null,
                    'start_district_name' => $startDistrict->name ?? 'Unknown',
                    'end_district_name' => $endDistrict->name ?? 'Unknown',
                    'route_display' => sprintf('%s → %s',
                        $startDistrict->name ?? 'Unknown',
                        $endDistrict->name ?? 'Unknown'
                    ),

                    // Boarding (starting) counter details
                    'boarding_counter' => $boardingCounter,

                    // Dropping (ending) counter details
                    'dropping_counter' => $droppingCounter,

                    // Driver details
                    'driver_id' => $trip->driver_id ?? null,
                    'driver_name' => $trip->driver_id ? $this->getEmployeeName($trip->driver_id) : null,
                    'driver_contact' => $trip->driver_id ? $this->getEmployeeContact($trip->driver_id) : null,
                    'driver_license' => $trip->driver_id ? $this->getEmployeeLicense($trip->driver_id) : null,

                    // Supervisor details
                    'supervisor_id' => $trip->supervisor_id ?? null,
                    'supervisor_name' => $trip->supervisor_id ? $this->getEmployeeName($trip->supervisor_id) : null,
                    'supervisor_contact' => $trip->supervisor_id ? $this->getEmployeeContact($trip->supervisor_id) : null,

                    // Seat plan details
                    'seat_plan_id' => $trip->seatPlan->id ?? null,
                    'seat_plan_name' => $trip->seatPlan->name ?? null,
                    'seat_plan_floors' => $trip->seatPlan->floor ?? null,
                    'seat_plan_rows' => $trip->seatPlan->rows ?? null,
                    'seat_plan_cols' => $trip->seatPlan->cols ?? null,

                    // Get total seats from seat_plans or calculate from floors
                    'total_seats' => $this->getTotalSeats($trip->seat_plan_id),

                    // Seat inventory summary using model method
                    'seat_inventory_summary' => $this->getSeatInventorySummary($trip),

                    // Fare information
                    'fare_info' => $fareInfo,

                    // Model state checks
                    'is_ac' => $trip->isAC(),
                    'is_active' => $trip->isActive(),
                    'is_migrated' => $trip->isMigrated(),

                    'created_at' => $trip->created_at,
                    'updated_at' => $trip->updated_at,
                ];
            });

            // Update the collection in paginated result
            $trips->setCollection($transformedTrips);

            return $this->successResponse([
                'trips' => $trips,
                'search_criteria' => [
                    'trip_date' => $tripDate->format('Y-m-d'),
                    'route_start_id' => (int) $routeStartId,
                    'route_end_id' => (int) $routeEndId,
                    'coach_no' => $request->coach_no,
                    'schedule_id' => $request->schedule_id ? (int) $request->schedule_id : null,
                ],
                'total_trips' => $trips->total(),

            ], 'Active trips retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search trips: ' . $e->getMessage(), 500);
        }
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



    public function seatRequest(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'seat_inventory_id' => 'required|integer',
                'trip_id'           => 'required|integer',
                'issue_id' => 'sometimes|string|max:100', // Optional - use existing or create new
                'notes' => 'sometimes|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $seatInventoryId = $request->seat_inventory_id;
            $userId = auth()->user()->id;
            $notes = $request->get('notes', '');
            $issueId = $request->get('issue_id') ?: $this->generateIssueId(); // Use provided or generate new
            $tripId = $request->trip_id;

            DB::beginTransaction();
            $seatInventory = SeatInventory::forTrip($tripId)
                ->where('id', $seatInventoryId)
                ->first();

            // Find the seat inventory record
            if (!$seatInventory) {
                return $this->errorResponse('Seat inventory not found', 404);
            }

            // Check if seat is available (status 1 = available)
            if ($seatInventory->booking_status != 1) {
                $statusText = match($seatInventory->booking_status) {
                    2 => 'already booked',
                    3 => 'currently blocked',
                    0 => 'cancelled/unavailable',
                    default => 'unavailable'
                };

                return $this->errorResponse("Seat is {$statusText}", 422);
            }

            // Check if seat is currently blocked by another user
            if ($seatInventory->blocked_until &&
                $seatInventory->blocked_until > now() &&
                $seatInventory->last_locked_user_id != $userId) {

                $blockedUntil = Carbon::parse($seatInventory->blocked_until);
                $remainingMinutes = $blockedUntil->diffInMinutes(now());

                return $this->errorResponse(
                    "Seat is currently blocked by another user for {$remainingMinutes} more minutes",
                    423
                );
            }

            // Update seat inventory - block for 5 minutes
            $blockedUntil = now()->addMinutes(5);
            $seatInventory->update([
                'blocked_until' => $blockedUntil,
                'last_locked_user_id' => $userId,
                'updated_at' => now(),
            ]);

            // Create seat request record
            $seatRequest = \DB::table('seat_requests')->insertGetId([
                'issue_id' => $issueId,
                'seat_inventory_id' => $seatInventoryId,
                'trip_id' => $seatInventory->trip_id,
                'seat_id' => $seatInventory->seat_id,
                'user_id' => $userId,
                'status' => 'pending',
                'blocked_until' => $blockedUntil,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get additional seat and trip information
            $seatInfo = $this->getSeatRequestInfo($seatRequest);

            // Get all seats in this issue
            $issueSeats = $this->getIssueSeats($issueId, $userId);

            // Get trip instance to access fare information
            $tripInstance = TripInstance::findAcrossPartitions($tripId);
            $fareInfo = null;

            if ($tripInstance && $tripInstance->fare) {
                $fareInfo = [
                    'fare_id' => $tripInstance->fare->id,
                    'amount' => $tripInstance->fare->amount ?? null, // Adjust field name as per your fare table
                    'coach_type' => $tripInstance->fare->coach_type_name,
                    'route_id' => $tripInstance->fare->route_id,
                    'seat_plan_id' => $tripInstance->fare->seat_plan_id,
                    'status' => $tripInstance->fare->status_name,
                ];
            }

            DB::commit();

            $response = [
                'seat_request_id' => $seatRequest,
                'issue_id' => $issueId,
                'seat_inventory_id' => $seatInventoryId,
                'status' => 'pending',
                'blocked_until' => $blockedUntil->toDateTimeString(),
                'blocked_for_minutes' => 5,
                'remaining_time' => [
                    'minutes' => 5,
                    'seconds' => 300,
                    'expires_at' => $blockedUntil->toDateTimeString(),
                ],
                'seat_info' => $seatInfo,
                'fare_info' => $fareInfo, // Added fare information
                'user_id' => $userId,
                'created_at' => now()->toISOString(),
                'issue_summary' => [
                    'issue_id' => $issueId,
                    'total_seats_in_issue' => count($issueSeats),
                    'seats' => $issueSeats,
                ],
            ];

            return $this->successResponse($response, 'Seat blocked successfully for 5 minutes', 201);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to request seat: ' . $e->getMessage(), 500);
        }
    }


    public function removeSeatRequest(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'seat_inventory_id' => 'required|integer',
                'issue_id' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $userId = auth()->user()->id;
            $seatInventoryId = $request->seat_inventory_id;
            $issueId = $request->issue_id;

            DB::beginTransaction();

            // Find seat request by seat_inventory_id + issue_id
            $seatRequest = \DB::table('seat_requests')
                ->where('seat_inventory_id', $seatInventoryId)
                ->where('issue_id', $issueId)
                ->where('user_id', $userId) // Ensure user can only remove their own requests
                ->where('status', 'pending') // Only pending requests can be cancelled
                ->first();

            if (!$seatRequest) {
                return $this->errorResponse('Seat request not found, already cancelled, or you do not have permission to remove it', 404);
            }

            // Find the seat inventory record
            $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                ->where('id', $seatInventoryId)
                ->first();

            if (!$seatInventory) {
                return $this->errorResponse('Seat inventory not found', 404);
            }

            // Verify that the seat is currently blocked by this user
            if ($seatInventory->last_locked_user_id != $userId) {
                return $this->errorResponse('You do not have permission to remove this seat request', 403);
            }

            // Soft delete the seat request by updating status to cancelled
            \DB::table('seat_requests')
                ->where('id', $seatRequest->id)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);

            // Clear only the blocked_until from seat inventory - make seat available again
            // Keep last_locked_user_id for audit purposes
            $seatInventory->update([
                'blocked_until' => null,
                'updated_at' => now(),
            ]);

            // Get remaining active seats in this issue
            $remainingSeats = \DB::table('seat_requests')
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->get();

            // Get seat info directly
            $seat = \DB::table('seats')->where('id', $seatRequest->seat_id)->first();
            $seatInfo = [
                'seat_id' => $seatRequest->seat_id,
                'seat_number' => $seat->seat_number ?? null,
                'row_position' => $seat->row_position ?? null,
                'col_position' => $seat->col_position ?? null,
                'seat_type' => $seat->seat_type ?? null,
            ];

            DB::commit();

            $response = [
                'cancelled_seat_request_id' => $seatRequest->id,
                'seat_inventory_id' => $seatInventoryId,
                'issue_id' => $issueId,
                'trip_id' => $seatRequest->trip_id,
                'seat_info' => $seatInfo,
                'seat_status' => 'available', // Seat is now available again
                'request_status' => 'cancelled',
                'blocked_until' => null,
                'user_id' => $userId,
                'cancelled_at' => now()->toISOString(),
                'remaining_seats_in_issue' => [
                    'issue_id' => $issueId,
                    'total_remaining_seats' => count($remainingSeats),
                    'seats' => $remainingSeats->map(function($seat) {
                        return [
                            'seat_request_id' => $seat->id,
                            'seat_inventory_id' => $seat->seat_inventory_id,
                            'seat_id' => $seat->seat_id,
                            'status' => $seat->status,
                            'blocked_until' => $seat->blocked_until,
                        ];
                    })->toArray(),
                ],
            ];

            return $this->successResponse($response, 'Seat request cancelled successfully', 200);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to cancel seat request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove all seats from an issue at once
     */
    public function removeAllSeatsFromIssue(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'issue_id' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $userId = auth()->user()->id;
            $issueId = $request->issue_id;

            DB::beginTransaction();

            // Get all pending seat requests for this issue and user
            $seatRequests = \DB::table('seat_requests')
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->get();

            if ($seatRequests->isEmpty()) {
                return $this->errorResponse('No pending seat requests found for this issue', 404);
            }

            $cancelledSeats = [];

            // Process each seat request
            foreach ($seatRequests as $seatRequest) {
                // Soft delete the seat request by updating status to cancelled
                \DB::table('seat_requests')
                    ->where('id', $seatRequest->id)
                    ->update([
                        'status' => 'cancelled',
                        'updated_at' => now(),
                    ]);

                // Clear blocked_until from seat inventory (keep last_locked_user_id)
                $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                    ->where('id', $seatRequest->seat_inventory_id)
                    ->where('last_locked_user_id', $userId)
                    ->first();

                if ($seatInventory) {
                    $seatInventory->update([
                        'blocked_until' => null,
                        'updated_at' => now(),
                    ]);

                    $cancelledSeats[] = [
                        'seat_request_id' => $seatRequest->id,
                        'seat_inventory_id' => $seatRequest->seat_inventory_id,
                        'seat_id' => $seatRequest->seat_id,
                        'status' => 'cancelled',
                    ];
                }
            }

            DB::commit();

            $response = [
                'issue_id' => $issueId,
                'cancelled_seats_count' => count($cancelledSeats),
                'cancelled_seats' => $cancelledSeats,
                'user_id' => $userId,
                'cancelled_at' => now()->toISOString(),
            ];

            return $this->successResponse($response, 'All seats cancelled from issue successfully', 200);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to cancel seats from issue: ' . $e->getMessage(), 500);
        }
    }

    private function generateIssueId()
    {
        return 'IE-' . now()->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function getIssueSeats($issueId, $userId)
    {
        return \DB::table('seat_requests as sr')
            ->leftJoin('seats as s', 'sr.seat_id', '=', 's.id')
            ->where('sr.issue_id', $issueId)
            ->where('sr.user_id', $userId)
            ->select([
                'sr.id as seat_request_id',
                'sr.seat_inventory_id',
                'sr.seat_id',
                'sr.status',
                's.seat_number',
                's.row_position',
                's.col_position',
                's.seat_type',
            ])
            ->get()
            ->toArray();
    }

    private function getSeatRequestInfo($seatRequestId)
    {
        try {
            $info = \DB::table('seat_requests as sr')
                ->leftJoin('seat_inventories as si', 'sr.seat_inventory_id', '=', 'si.id')
                ->leftJoin('seats as s', 'sr.seat_id', '=', 's.id')
                ->leftJoin('trip_instances_' . now()->format('Ym') . ' as ti', 'sr.trip_id', '=', 'ti.id')
                ->leftJoin('routes as r', 'ti.route_id', '=', 'r.id')
                ->leftJoin('districts as sd', 'r.start_id', '=', 'sd.id')
                ->leftJoin('districts as ed', 'r.end_id', '=', 'ed.id')
                ->leftJoin('coaches as c', 'ti.coach_id', '=', 'c.id')
                ->where('sr.id', $seatRequestId)
                ->select([
                    's.seat_number',
                    's.row_position',
                    's.col_position',
                    's.seat_type',
                    'ti.trip_date',
                    'ti.coach_type',
                    'c.coach_no',
                    'r.distance',
                    'r.duration',
                    'sd.name as start_district',
                    'ed.name as end_district',
                    'si.booking_status',
                ])
                ->first();

            if (!$info) {
                return null;
            }

            return [
                'seat' => [
                    'seat_number' => $info->seat_number,
                    'row_position' => $info->row_position,
                    'col_position' => $info->col_position,
                    'seat_type' => $info->seat_type,
                ],
                'trip' => [
                    'trip_date' => $info->trip_date,
                    'coach_no' => $info->coach_no,
                    'coach_type' => $info->coach_type,
                    'coach_type_name' => $info->coach_type == 1 ? 'AC' : 'Non-AC',
                ],
                'route' => [
                    'start_district' => $info->start_district,
                    'end_district' => $info->end_district,
                    'route_display' => ($info->start_district ?? 'Unknown') . ' → ' . ($info->end_district ?? 'Unknown'),
                    'distance' => $info->distance,
                    'duration' => $info->duration,
                ],
                'current_status' => [
                    'booking_status' => $info->booking_status,
                    'status_name' => match($info->booking_status) {
                        1 => 'Available',
                        2 => 'Booked',
                        3 => 'Blocked',
                        0 => 'Cancelled',
                        default => 'Unknown'
                    },
                ],
            ];

        } catch (\Exception $e) {
            return null;
        }
    }
}
