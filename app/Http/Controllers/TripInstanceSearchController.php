<?php

namespace App\Http\Controllers;

use App\Helpers\TripHelper;
use App\Models\Counter;
use App\Models\SeatInventory;
use App\Models\TripInstance;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TripInstanceSearchController extends Controller
{
    use ApiResponse;

    public function searchTrips(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'trip_date' => 'required|date|date_format:Y-m-d',
                'route_start_id' => 'required|integer',
                'route_end_id' => 'required|integer',
                'coach_no' => 'sometimes|string|max:50',
                'schedule_id' => 'sometimes|integer',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'coach_type' => 'sometimes|in:1,2',
                'boarding_counter_id' => 'sometimes|integer|exists:counters,id',
                'dropping_counter_id' => 'sometimes|integer|exists:counters,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $tripDate = Carbon::parse($request->trip_date);
            $routeStartId = $request->route_start_id;
            $routeEndId = $request->route_end_id;
            $coachType = $request->coach_type ?? null;
            $boardingCounterId = $request->boarding_counter_id ?? null;
            $droppingCounterId = $request->dropping_counter_id ?? null;

            $query = TripInstance::forDate($tripDate)
                ->active()
                ->byDate($tripDate)
                ->with(['fares', 'boardingDroppings.counter'])
                ->whereHas('route', function ($routeQuery) use ($routeStartId, $routeEndId) {
                    $routeQuery->where('start_id', $routeStartId)
                        ->where('end_id', $routeEndId);
                });

            if ($request->filled('coach_no')) {
                $query->whereHas('coach', function ($coachQuery) use ($request) {
                    $coachQuery->where('coach_no', $request->coach_no);
                });
            }

            if ($request->filled('schedule_id')) {
                $query->where('schedule_id', $request->schedule_id);
            }

            if ($coachType && in_array($coachType, [1, 2])) {
                $query->where('coach_type', $coachType);
            }

            if ($boardingCounterId) {
                $query->whereHas('boardingDropping', function ($subQuery) use ($boardingCounterId) {
                    $subQuery->where('counter_id', $boardingCounterId)->where('type', 1);
                });
            }

            if ($droppingCounterId) {
                $query->whereHas('boardingDropping', function ($subQuery) use ($droppingCounterId) {
                    $subQuery->where('counter_id', $droppingCounterId)->where('type', 2);
                });
            }

            $query->orderBy('trip_date', 'asc')->orderBy('created_at', 'desc');

            $tripIds = (clone $query)->pluck('id')->toArray();

            $perPage = $request->get('per_page', 15);
            $trips = $query->paginate($perPage);

            $transformedTrips = $trips->getCollection()->map(function ($trip) {
                $startDistrict = DB::table('districts')->where('id', $trip->route->start_id)->first();
                $endDistrict = DB::table('districts')->where('id', $trip->route->end_id)->first();

                $boardingCounter = null;
                $startingPoint = $trip->boardingDroppings->where('starting_point_status', 1)->first();
                if ($startingPoint && $startingPoint->counter) {
                    $boardingCounter = [
                        'id' => $startingPoint->counter->id,
                        'name' => $startingPoint->counter->address,
                        'location' => $startingPoint->counter->land_mark,
                        'contact' => $startingPoint->counter->mobile ?? null,
                        'time' => $startingPoint->time,
                    ];
                }

                $droppingCounter = null;
                $endingPoint = $trip->boardingDroppings->where('ending_point_status', 1)->first();
                if ($endingPoint && $endingPoint->counter) {
                    $droppingCounter = [
                        'id' => $endingPoint->counter->id,
                        'name' => $endingPoint->counter->address,
                        'location' => $endingPoint->counter->land_mark,
                        'contact' => $endingPoint->counter->mobile ?? null,
                        'time' => $endingPoint->time,
                    ];
                }

                $faresInfo = [];
                if ($trip->fares && $trip->fares->count() > 0) {
                    $faresInfo = $trip->fares->map(function ($fare) {
                        return [
                            'fare_id' => $fare->id,
                            'seat_type' => $fare->seat_type,
                            'amount' => $fare->amount ?? null,
                            'coach_type' => $fare->coach_type_name,
                            'route_id' => $fare->route_id,
                            'seat_plan_id' => $fare->seat_plan_id,
                            'status' => $fare->status_name,
                            'from_date' => $fare->from_date ? $fare->from_date->format('Y-m-d H:i:s') : null,
                            'to_date' => $fare->to_date ? $fare->to_date->format('Y-m-d H:i:s') : null,
                        ];
                    })->toArray();
                }

                $defaultFare = $trip->getDefaultFare();
                $defaultFareInfo = null;
                if ($defaultFare) {
                    $defaultFareInfo = [
                        'fare_id' => $defaultFare->id,
                        'seat_type' => $defaultFare->seat_type,
                        'amount' => $defaultFare->amount ?? null,
                        'coach_type' => $defaultFare->coach_type_name,
                    ];
                }

                $seatInventorySummary = $this->getSeatInventorySummaryWithSold($trip);

                return [
                    'trip_id' => $trip->id,
                    'trip_date' => $trip->formatted_trip_date,
                    'trip_date_formatted' => $trip->trip_date->format('l, F j, Y'),
                    'status' => $trip->status,
                    'status_name' => $trip->status_name,
                    'coach_type' => $trip->coach_type,
                    'coach_type_name' => $trip->coach_type_name,
                    'current_partition' => $trip->current_partition,
                    'coach_id' => $trip->coach->id ?? null,
                    'coach_no' => $trip->coach->coach_no ?? null,
                    'coach_seat_plan_id' => $trip->coach->seat_plan_id ?? null,
                    'coach_status' => $trip->coach->status ?? null,
                    'bus_id' => $trip->bus->id ?? null,
                    'bus_registration_number' => $trip->bus->registration_number ?? null,
                    'bus_manufacturer' => $trip->bus->manufacturer_company ?? null,
                    'bus_model_year' => $trip->bus->model_year ?? null,
                    'schedule_id' => $trip->schedule->id ?? null,
                    'schedule_name' => $trip->schedule->name ?? null,
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
                    'boarding_counter' => $boardingCounter,
                    'dropping_counter' => $droppingCounter,
                    'driver_id' => $trip->driver_id ?? null,
                    'driver_name' => TripHelper::getEmployeeName($trip->driver_id),
                    'driver_contact' => TripHelper::getEmployeeContact($trip->driver_id),
                    'driver_license' => TripHelper::getEmployeeLicense($trip->driver_id),
                    'supervisor_id' => $trip->supervisor_id ?? null,
                    'supervisor_name' => TripHelper::getEmployeeName($trip->supervisor_id),
                    'supervisor_contact' => TripHelper::getEmployeeContact($trip->supervisor_id),
                    'seat_plan_id' => $trip->seatPlan->id ?? null,
                    'seat_plan_name' => $trip->seatPlan->name ?? null,
                    'seat_plan_floors' => $trip->seatPlan->floor ?? null,
                    'seat_plan_rows' => $trip->seatPlan->rows ?? null,
                    'seat_plan_cols' => $trip->seatPlan->cols ?? null,
                    'total_seats' => TripHelper::getTotalSeats($trip->seat_plan_id),
                    'seat_inventory_summary' => $seatInventorySummary,
                    'sold_seats_count' => $seatInventorySummary['sold'] ?? 0,
                    'available_seats_count' => $seatInventorySummary['available'] ?? 0,
                    'booked_seats_count' => $seatInventorySummary['booked'] ?? 0,
                    'blocked_seats_count' => $seatInventorySummary['blocked'] ?? 0,
                    'cancelled_seats_count' => $seatInventorySummary['cancelled'] ?? 0,
                    'availability_percentage' => $this->calculateAvailabilityPercentage($seatInventorySummary),
                    'fares' => $faresInfo,
                    'available_seat_types' => $trip->getAvailableSeatTypes(),
                    'is_ac' => $trip->isAC(),
                    'is_active' => $trip->isActive(),
                    'is_migrated' => $trip->isMigrated(),
                    'created_at' => $trip->created_at,
                    'updated_at' => $trip->updated_at,
                ];
            });

            $trips->setCollection($transformedTrips);

            $boardingCounters = Counter::where('status', 1)->whereHas('tripBoardingDroppings', function ($query) use ($tripIds) {
                $query->whereIn('trip_id', $tripIds)->where('type', 1);
            })->get();

            $droppingCounters = Counter::where('status', 1)->whereHas('tripBoardingDroppings', function ($query) use ($tripIds) {
                $query->whereIn('trip_id', $tripIds)->where('type', 2);
            })->get();

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
                'boarding_counters' => $boardingCounters,
                'dropping_counters' => $droppingCounters,
            ], 'Active trips retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search trips: '.$e->getMessage(), 500);
        }
    }

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

    private function getSeatInventorySummaryWithSold($trip): array
    {
        try {
            $seatInventories = SeatInventory::forTrip($trip->id)->get();

            $summary = [
                'total' => $seatInventories->count(),
                'available' => $seatInventories->where('booking_status', SeatInventory::STATUS_AVAILABLE)->count(),
                'booked' => $seatInventories->where('booking_status', SeatInventory::STATUS_BOOKED)->count(),
                'blocked' => $seatInventories->where('booking_status', SeatInventory::STATUS_BLOCKED)->count(),
                'cancelled' => $seatInventories->where('booking_status', SeatInventory::STATUS_CANCELLED)->count(),
                'sold' => $seatInventories->where('booking_status', SeatInventory::STATUS_SOLD)->count(),
            ];

            $summary['occupied'] = $summary['booked'] + $summary['sold'];

            return $summary;
        } catch (\Exception $e) {
            \Log::error("Failed to get seat inventory summary for trip {$trip->id}: ".$e->getMessage());

            return ['total' => 0, 'available' => 0, 'booked' => 0, 'blocked' => 0, 'cancelled' => 0, 'sold' => 0, 'occupied' => 0];
        }
    }

    private function calculateAvailabilityPercentage(array $seatInventorySummary): float
    {
        $total = $seatInventorySummary['total'] ?? 0;
        $available = $seatInventorySummary['available'] ?? 0;

        if ($total === 0) {
            return 0;
        }

        return round(($available / $total) * 100, 2);
    }

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
