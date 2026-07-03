<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\TripInstance\SearchTripsRequest;
use App\Models\TripInstance;
use App\Services\TripSearchService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TripInstanceSearchController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TripSearchService $tripSearchService) {}

    /**
     * Search active trips by date, route, and optional filters.
     *
     * @param  SearchTripsRequest  $request
     * @return JsonResponse
     */
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

    /**
     * Get all trip instances for a specific date from the correct monthly partition.
     *
     * @param  string  $date  Date in Y-m-d format
     * @return JsonResponse
     */
    public function getByDate(string $date): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tripInstances = TripInstance::forDate($date)
                ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor'])
                ->byDate($date)
                ->get();

            DB::commit();

            return $this->successResponse([
                'date' => $date,
                'total_records' => $tripInstances->count(),
                'data' => $tripInstances,
            ], 'Trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve trip instances: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get all active trip instances scheduled for today.
     *
     * @return JsonResponse
     */
    public function getToday(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tripInstances = TripInstance::forDate(today())
                ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor'])
                ->today()
                ->active()
                ->get();

            DB::commit();

            return $this->successResponse([
                'date' => today()->format('Y-m-d'),
                'total_records' => $tripInstances->count(),
                'data' => $tripInstances,
            ], "Today's trip instances retrieved successfully");

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse("Failed to retrieve today's trip instances: ".$e->getMessage(), 500);
        }
    }

    /**
     * Get trip instances across a date range, spanning multiple monthly partitions if needed.
     *
     * @param  string  $startDate  Start date in Y-m-d format
     * @param  string  $endDate  End date in Y-m-d format
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getByDateRange(string $startDate, string $endDate, Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $tripInstance = new TripInstance;
            $rawQuery = $tripInstance->queryAcrossPartitions($start, $end);

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

            $sortBy = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $rawQuery->orderBy($sortBy, $sortOrder);

            $tripInstances = $rawQuery->get();

            DB::commit();

            return $this->successResponse([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_records' => $tripInstances->count(),
                'data' => $tripInstances,
            ], 'Trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve trip instances: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get paginated trip instances from a specific monthly partition table.
     *
     * @param  string  $yearMonth  Partition month in Y-m format (e.g. 2025-06)
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getByPartition(string $yearMonth, Request $request): JsonResponse
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

            $sortBy = $request->get('sort_by', 'trip_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $tripInstances = $query->paginate($perPage);

            DB::commit();

            return $this->successResponse([
                'partition' => $yearMonth,
                'partition_table' => TripInstance::forDate($date)->getTable(),
                'data' => $tripInstances,
            ], 'Partition trip instances retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve partition trip instances: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get statistics and health information for all trip instance partition tables.
     *
     * @return JsonResponse
     */
    public function getPartitionInfo(): JsonResponse
    {
        try {
            $tripInstance = new TripInstance;
            $allPartitions = $tripInstance->getAllPartitionTables();

            $statistics = [];
            $totalRecords = 0;
            $totalSizeMB = 0;

            foreach ($allPartitions as $partition) {
                try {
                    $count = DB::table($partition)->count();
                    $size = DB::select('
                        SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                        FROM information_schema.tables
                        WHERE table_schema = DATABASE() AND table_name = ?
                    ', [$partition])[0]->size_mb ?? 0;

                    $month = str_replace('trip_instances_', '', $partition);
                    $formattedMonth = (strlen($month) === 6 && is_numeric($month))
                        ? Carbon::createFromFormat('Ym', $month)->format('Y-m')
                        : 'Unknown';

                    $statistics[] = [
                        'table' => $partition,
                        'month' => $formattedMonth,
                        'record_count' => $count,
                        'size_mb' => (float) $size,
                    ];

                    $totalRecords += $count;
                    $totalSizeMB += (float) $size;
                } catch (\Exception $e) {
                    continue;
                }
            }

            $currentMonth = now()->format('Ym');
            $nextMonth = now()->addMonth()->format('Ym');

            $currentPartitionExists = in_array('trip_instances_'.$currentMonth, $allPartitions);
            $nextPartitionExists = in_array('trip_instances_'.$nextMonth, $allPartitions);

            usort($statistics, fn ($a, $b) => strcmp($a['month'], $b['month']));

            return $this->successResponse([
                'total_partitions' => count($allPartitions),
                'total_records' => $totalRecords,
                'total_size_mb' => round($totalSizeMB, 2),
                'current_month_partition_exists' => $currentPartitionExists,
                'next_month_partition_exists' => $nextPartitionExists,
                'current_month' => now()->format('Y-m'),
                'next_month' => now()->addMonth()->format('Y-m'),
                'recommendations' => $this->getPartitionRecommendations($currentPartitionExists, $nextPartitionExists, count($allPartitions)),
                'partitions' => $statistics,
            ], 'Partition information retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve partition information: '.$e->getMessage(), 500);
        }
    }

    /**
     * Build advisory messages about the current partition setup health.
     *
     * @param  bool  $currentExists
     * @param  bool  $nextExists
     * @param  int  $totalPartitions
     * @return array<int, array{type: string, message: string}>
     */
    private function getPartitionRecommendations(bool $currentExists, bool $nextExists, int $totalPartitions): array
    {
        $recommendations = [];

        if (! $currentExists) {
            $recommendations[] = ['type' => 'warning', 'message' => 'Current month partition does not exist. It will be created automatically when needed.'];
        }

        if (! $nextExists) {
            $recommendations[] = ['type' => 'info', 'message' => 'Next month partition does not exist. Consider creating it in advance for better performance.'];
        }

        if ($totalPartitions > 24) {
            $recommendations[] = ['type' => 'suggestion', 'message' => 'You have many partitions ('.$totalPartitions.'). Consider archiving partitions older than 2 years.'];
        }

        if (empty($recommendations)) {
            $recommendations[] = ['type' => 'success', 'message' => 'Partition setup looks healthy!'];
        }

        return $recommendations;
    }
}
