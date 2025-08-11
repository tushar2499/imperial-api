<?php

namespace App\Http\Controllers;

use App\Models\TripInstance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
            DB::beginTransaction();

            // Determine query strategy based on parameters
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : null;
            $tripDate = $request->filled('trip_date') ? Carbon::parse($request->trip_date) : null;

            // Query specific date partition (auto-creates if needed)
            if ($tripDate) {
                $query = TripInstance::forDate($tripDate);
            }
            // Query across multiple partitions for date range (auto-creates if needed)
            else if ($startDate && $endDate) {
                // This will auto-create partitions for the date range
                $tripInstance = new TripInstance();
                $rawQuery = $tripInstance->queryAcrossPartitions($startDate, $endDate);

                // Apply filters to raw query
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
                $sortBy = $request->get('sort_by', 'trip_date');
                $sortOrder = $request->get('sort_order', 'desc');
                $rawQuery->orderBy($sortBy, $sortOrder);

                // Get results and convert to collection
                $rawResults = $rawQuery->get();
                $tripInstances = $rawResults->map(function ($item) {
                    $model = new TripInstance();
                    $model->setRawAttributes((array) $item, true);
                    return $model;
                });

                // Manual pagination for cross-partition results
                $page = $request->get('page', 1);
                $perPage = $request->get('per_page', 15);
                $total = $tripInstances->count();
                $items = $tripInstances->forPage($page, $perPage)->values();

                $paginatedData = [
                    'current_page' => (int) $page,
                    'data' => $items,
                    'first_page_url' => request()->url() . '?page=1',
                    'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
                    'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                    'last_page_url' => request()->url() . '?page=' . ($total > 0 ? ceil($total / $perPage) : 1),
                    'next_page_url' => $page < ceil($total / $perPage) ? request()->url() . '?page=' . ($page + 1) : null,
                    'path' => request()->url(),
                    'per_page' => $perPage,
                    'prev_page_url' => $page > 1 ? request()->url() . '?page=' . ($page - 1) : null,
                    'to' => min($page * $perPage, $total),
                    'total' => $total
                ];

                DB::commit();
                return $this->successResponse($paginatedData, 'Trip instances retrieved successfully');
            }
            // Default to current month partition (auto-creates if needed)
            else {
                $query = TripInstance::forCurrentMonth();
            }

            // Add relationships
            $query->with([
                'coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'migratedTrip'
            ]);

            // Apply filters for single partition queries
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

            // Special date filters
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
            $sortBy = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $tripInstances = $query->paginate($perPage);

            DB::commit();

            return $this->successResponse($tripInstances, 'Trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve trip instances: ' . $e->getMessage(), 500);
        }
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
                'coach_id' => 'required|integer',
                'bus_id' => 'required|integer',
                'schedule_id' => 'required|integer',
                'seat_plan_id' => 'required|integer',
                'route_id' => 'required|integer',
                'coach_type' => 'required|in:1,2',
                'trip_date' => 'required|date',
                'driver_id' => 'nullable|integer',
                'supervisor_id' => 'nullable|integer',
                'status' => 'sometimes|in:0,1,2',
                'migrated_trip_id' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            DB::beginTransaction();

            $tripDate = $request->input('trip_date');

            // Create trip instance with auto-partitioning
            $tripInstance = TripInstance::create([
                'coach_id' => $request->input('coach_id'),
                'bus_id' => $request->input('bus_id'),
                'schedule_id' => $request->input('schedule_id'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'route_id' => $request->input('route_id'),
                'coach_type' => $request->input('coach_type'),
                'driver_id' => $request->input('driver_id'),
                'supervisor_id' => $request->input('supervisor_id'),
                'trip_date' => $tripDate,
                'status' => $request->input('status', 1),
                'migrated_trip_id' => $request->input('migrated_trip_id'),
                'created_by' => auth()->check() ? auth()->user()->id : null,
            ]);

            DB::commit();

            return $this->successResponse([
                'data' => $tripInstance,
                'message' => 'Trip instance created successfully'
            ], 'Trip instance created successfully', 201);

        } catch (\Exception $e) {
            DB::rollback();

            // Return detailed error for debugging
            return response()->json([
                'success' => false,
                'message' => 'Failed to create trip instance',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Display the specified trip instance
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Search across all partitions (auto-creates if needed during search)
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (!$tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            // Load relationships
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan', 'route',
                'driver', 'supervisor', 'migratedTrip', 'creator', 'updater', 'migrator'
            ]);

            return $this->successResponse($tripInstance, 'Trip instance retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve trip instance: ' . $e->getMessage(), 500);
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
            'coach_id' => 'sometimes|exists:coaches,id',
            'bus_id' => 'sometimes|exists:buses,id',
            'schedule_id' => 'sometimes|exists:schedules,id',
            'seat_plan_id' => 'sometimes|exists:seat_plans,id',
            'route_id' => 'sometimes|exists:routes,id',
            'coach_type' => 'sometimes|in:1,2',
            'driver_id' => 'nullable|exists:employees,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'trip_date' => 'sometimes|date',
            'status' => 'sometimes|in:0,1,2',
            'migrated_trip_id' => 'nullable|integer'
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

            // If trip_date is being changed, handle partition migration
            if ($request->filled('trip_date') && $request->input('trip_date') != $tripInstance->trip_date->format('Y-m-d')) {
                $newTripDate = $request->input('trip_date');
                $newPartitionTable = (new TripInstance())->getPartitionTableName($newTripDate);
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

                // If partition changes, create new record and delete old one
                if ($newPartitionTable !== $currentPartitionTable) {
                    // Prepare data for new record
                    $updateData = $tripInstance->toArray();
                    unset($updateData['id']);

                    // Apply updates
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

            // Refresh and load relationships
            $tripInstance = $tripInstance->fresh();
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'migratedTrip'
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
            'migrated_trip_id' => 'required|integer|different:' . $id
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
                'status' => TripInstance::STATUS_MIGRATED,
                'migrated_trip_id' => $request->input('migrated_trip_id'),
                'migrated_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'updated_at' => now(),
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
                'status' => $newStatus,
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
                'date' => $date,
                'total_records' => $tripInstances->count(),
                'data' => $tripInstances
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
                'date' => today()->format('Y-m-d'),
                'total_records' => $tripInstances->count(),
                'data' => $tripInstances
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
            $end = Carbon::parse($endDate);

            // Get raw data from multiple partitions (auto-creates partitions)
            $tripInstance = new TripInstance();
            $rawQuery = $tripInstance->queryAcrossPartitions($start, $end);

            // Apply additional filters
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
            $sortBy = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $rawQuery->orderBy($sortBy, $sortOrder);

            $tripInstances = $rawQuery->get();

            DB::commit();

            return $this->successResponse([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_records' => $tripInstances->count(),
                'data' => $tripInstances
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
                'year_month' => 'required|date_format:Y-m'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $date = Carbon::createFromFormat('Y-m', $yearMonth);

            // This will auto-create partition if needed
            $query = TripInstance::forDate($date)
                ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor']);

            // Apply filters
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
            $sortBy = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $tripInstances = $query->paginate($perPage);

            DB::commit();

            return $this->successResponse([
                'partition' => $yearMonth,
                'partition_table' => TripInstance::forDate($date)->getTable(),
                'data' => $tripInstances
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
            $tripInstance = new TripInstance();
            $allPartitions = $tripInstance->getAllPartitionTables();

            $statistics = [];
            $totalRecords = 0;
            $totalSizeMB = 0;

            foreach ($allPartitions as $partition) {
                try {
                    $count = DB::table($partition)->count();
                    $size = DB::select("
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
                        'table' => $partition,
                        'month' => $formattedMonth,
                        'record_count' => $count,
                        'size_mb' => (float) $size
                    ];

                    $totalRecords += $count;
                    $totalSizeMB += (float) $size;
                } catch (\Exception $e) {
                    // Skip if table query fails
                    continue;
                }
            }

            $currentMonth = now()->format('Ym');
            $nextMonth = now()->addMonth()->format('Ym');

            $currentPartitionExists = in_array('trip_instances_' . $currentMonth, $allPartitions);
            $nextPartitionExists = in_array('trip_instances_' . $nextMonth, $allPartitions);

            // Sort statistics by month
            usort($statistics, function($a, $b) {
                return strcmp($a['month'], $b['month']);
            });

            return $this->successResponse([
                'total_partitions' => count($allPartitions),
                'total_records' => $totalRecords,
                'total_size_mb' => round($totalSizeMB, 2),
                'current_month_partition_exists' => $currentPartitionExists,
                'next_month_partition_exists' => $nextPartitionExists,
                'current_month' => now()->format('Y-m'),
                'next_month' => now()->addMonth()->format('Y-m'),
                'recommendations' => $this->getPartitionRecommendations($currentPartitionExists, $nextPartitionExists, count($allPartitions)),
                'partitions' => $statistics
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
                'type' => 'warning',
                'message' => 'Current month partition does not exist. It will be created automatically when needed.'
            ];
        }

        if (!$nextExists) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Next month partition does not exist. Consider creating it in advance for better performance.'
            ];
        }

        if ($totalPartitions > 24) {
            $recommendations[] = [
                'type' => 'suggestion',
                'message' => 'You have many partitions (' . $totalPartitions . '). Consider archiving partitions older than 2 years.'
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Partition setup looks healthy!'
            ];
        }

        return $recommendations;
    }
}
