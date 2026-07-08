<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\Coach;
use App\Models\CoachConfiguration;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\SeatInventory;
use App\Models\SeatPlan;
use App\Models\SeatRequest;
use App\Models\TransportRoute;
use App\Models\TripBoardingDropping;
use App\Models\TripInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripInstanceService
{
    public function __construct(private readonly SeatInventoryService $seatInventoryService) {}

    /**
     * Paginated listing of trip instances. Routes to single-partition (Eloquent) or
     * cross-partition (raw UNION) depending on whether a date range spans months.
     */
    public function paginate(array $attributes): LengthAwarePaginator
    {
        $perPage = min((int) ($attributes['per_page'] ?? 15), 1000);
        $page = max((int) ($attributes['page'] ?? 1), 1);

        $tripDate = ! empty($attributes['trip_date']) ? Carbon::parse($attributes['trip_date']) : null;
        $startDate = ! empty($attributes['start_date']) ? Carbon::parse($attributes['start_date']) : null;
        $endDate = ! empty($attributes['end_date']) ? Carbon::parse($attributes['end_date']) : null;

        if ($startDate && $endDate) {
            return $this->paginateCrossPartition($attributes, $startDate, $endDate, $perPage, $page);
        }

        return $this->paginateSinglePartition($attributes, $tripDate, $perPage);
    }

    /**
     * Get all active trip instances for a given date partition (defaults to today).
     */
    public function allActive(?string $tripDate = null): Collection
    {
        $date = $tripDate ? Carbon::parse($tripDate) : today();

        return TripInstance::forDate($date)
            ->where('status', TripInstance::STATUS_ACTIVE)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Create a trip instance with partitions, optional seat inventory, and boarding/dropping points.
     *
     * @throws \RuntimeException
     */
    public function store(array $attributes): array
    {
        $tripDate = $attributes['trip_date'];

        $tempTrip = new TripInstance;
        if (! $tempTrip->ensurePartitionExists($tripDate)) {
            throw new \RuntimeException('Failed to create trip partition for trip date.');
        }

        $tempSeat = new SeatInventory;
        if (! $tempSeat->ensurePartitionExists($tripDate)) {
            throw new \RuntimeException('Failed to create seat inventory partition for trip date.');
        }

        return DB::transaction(function () use ($attributes, $tripDate) {
            $coachConfig = CoachConfiguration::findOrFail($attributes['coach_configuration_id']);

            $duplicate = TripInstance::forDate($tripDate)
                ->where('coach_id', $coachConfig->coach_id)
                ->where('schedule_id', $attributes['schedule_id'])
                ->whereDate('trip_date', $tripDate)
                ->first();

            if ($duplicate) {
                throw new \RuntimeException('A trip instance already exists for this coach, schedule, and date.');
            }

            $tripInstance = TripInstance::create([
                'coach_id' => $coachConfig->coach_id,
                'bus_id' => $coachConfig->bus_id,
                'schedule_id' => $attributes['schedule_id'],
                'seat_plan_id' => $coachConfig->seat_plan_id,
                'route_id' => $attributes['transport_route_id'],
                'coach_type' => $coachConfig->coach_type,
                'driver_id' => $attributes['driver_id'] ?? null,
                'supervisor_id' => $attributes['supervisor_id'] ?? null,
                'trip_date' => $tripDate,
                'status' => $attributes['status'] ?? 1,
                'migrated_trip_id' => $attributes['migrated_trip_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $autoCreate = $attributes['auto_create_seat_inventory'] ?? true;
            $seatInventoryResult = null;

            if ($autoCreate) {
                $seatInventoryResult = $this->seatInventoryService->createSeatInventoryForTrip(
                    $tripInstance->id,
                    $tripInstance->seat_plan_id
                );

                if (! $seatInventoryResult['success']) {
                    throw new \RuntimeException('Failed to create seat inventory: '.$seatInventoryResult['message']);
                }
            }

            foreach ($attributes['boarding_dropping_points'] as $point) {
                TripBoardingDropping::create([
                    'trip_id' => $tripInstance->id,
                    'counter_id' => $point['counter_id'],
                    'type' => $point['type'],
                    'time' => $point['time'],
                    'starting_point_status' => $point['starting_point_status'] ?? 0,
                    'ending_point_status' => $point['ending_point_status'] ?? 0,
                    'status' => $point['status'] ?? 1,
                    'created_by' => auth()->id(),
                ]);
            }

            return [
                'trip_instance' => $tripInstance,
                'seat_inventory_created' => $autoCreate,
                'seat_inventory' => $seatInventoryResult['data'] ?? null,
            ];
        });
    }

    /**
     * Show a trip instance with all related data and seat inventory.
     *
     * @throws ModelNotFoundException
     */
    public function showById(int $id): TripInstance
    {
        $tripInstance = TripInstance::findAcrossPartitions($id, now());

        if (! $tripInstance) {
            throw new ModelNotFoundException("Trip instance with ID {$id} not found.");
        }

        $tripInstance->load([
            'coach', 'bus', 'schedule', 'seatPlan.floors.seats', 'route',
            'driver', 'supervisor', 'migratedTrip', 'creator', 'updater', 'migrator',
            'boardingDroppings.counter',
            'fares' => function ($query) use ($tripInstance) {
                $query->where('seat_plan_id', $tripInstance->seat_plan_id)
                    ->where('coach_type', $tripInstance->coach_type)
                    ->where('status', 1);
            },
        ]);
        $tripInstance->seatPlan?->loadCount('seats');

        $seatInventoryData = $this->loadSeatInventory($id, $tripInstance);

        $tripInstance->setAttribute('seat_inventory', $seatInventoryData['data']);
        $tripInstance->setAttribute('fares_info', $this->formatFares($tripInstance));
        $tripInstance->setAttribute('partition_info', [
            'current_table' => $tripInstance->getTable(),
            'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
            'partition_month' => $tripInstance->trip_date->format('Y-m'),
            'seat_inventory_auto_created' => $seatInventoryData['auto_created'],
            'seat_inventory_count' => count($seatInventoryData['data']),
        ]);

        return $tripInstance;
    }

    /**
     * Find a trip instance by ID across all partitions.
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id, ?string $hintDate = null): TripInstance
    {
        $hint = $hintDate ? Carbon::parse($hintDate) : null;
        $tripInstance = TripInstance::findAcrossPartitions($id, $hint);

        if (! $tripInstance) {
            throw new ModelNotFoundException("Trip instance with ID {$id} not found.");
        }

        return $tripInstance;
    }

    /**
     * Update a trip instance, handling cross-partition moves when trip_date changes.
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): TripInstance
    {
        // Normalise field name to match store() and the DB column
        if (isset($attributes['transport_route_id'])) {
            $attributes['route_id'] = $attributes['transport_route_id'];
            unset($attributes['transport_route_id']);
        }

        // Resolve coach configuration — only when explicitly provided and non-null
        if (array_key_exists('coach_configuration_id', $attributes) && $attributes['coach_configuration_id'] !== null) {
            $coachConfig = CoachConfiguration::findOrFail($attributes['coach_configuration_id']);
            $attributes['coach_id'] = $coachConfig->coach_id;
            $attributes['bus_id'] = $coachConfig->bus_id;
            $attributes['seat_plan_id'] = $coachConfig->seat_plan_id;
            $attributes['coach_type'] = $coachConfig->coach_type;
        }
        unset($attributes['coach_configuration_id']);

        return DB::transaction(function () use ($id, $attributes) {
            $tripInstance = $this->findById($id);

            $updateFields = ['coach_id', 'bus_id', 'schedule_id', 'seat_plan_id', 'route_id',
                'coach_type', 'driver_id', 'supervisor_id', 'trip_date', 'status', 'migrated_trip_id'];

            $newTripDate = $attributes['trip_date'] ?? null;
            $dateChanged = $newTripDate && $newTripDate !== $tripInstance->trip_date->format('Y-m-d');

            if ($dateChanged) {
                $newPartitionTable = (new TripInstance)->getPartitionTableName($newTripDate);

                $duplicate = TripInstance::forDate($newTripDate)
                    ->where('coach_id', $attributes['coach_id'] ?? $tripInstance->coach_id)
                    ->where('schedule_id', $attributes['schedule_id'] ?? $tripInstance->schedule_id)
                    ->whereDate('trip_date', $newTripDate)
                    ->where('id', '!=', $id)
                    ->first();

                if ($duplicate) {
                    throw new \RuntimeException('A trip instance already exists for this coach, schedule, and date.');
                }

                if ($newPartitionTable !== $tripInstance->getTable()) {
                    // Ensure target partitions exist (DDL — runs outside transaction scope in MySQL)
                    (new TripInstance)->ensurePartitionExists($newTripDate);
                    (new SeatInventory)->ensurePartitionExists($newTripDate);

                    // Snapshot seat inventories BEFORE the old trip is removed
                    $oldInventories = SeatInventory::forTrip($tripInstance->id)->get();

                    // Build a clean column-only payload — toArray() includes appended/relation data
                    $columns = ['coach_id', 'bus_id', 'schedule_id', 'seat_plan_id', 'route_id',
                        'coach_type', 'driver_id', 'supervisor_id', 'trip_date', 'status',
                        'migrated_trip_id', 'created_by', 'migrated_by'];
                    $data = array_intersect_key($tripInstance->attributesToArray(), array_flip($columns));

                    foreach ($updateFields as $field) {
                        if (array_key_exists($field, $attributes)) {
                            $data[$field] = $attributes[$field];
                        }
                    }

                    $data['updated_by'] = auth()->id();
                    $newTripInstance = TripInstance::create($data);

                    // Migrate seat inventories to new partition (preserving booking state)
                    foreach ($oldInventories as $inv) {
                        SeatInventory::create([
                            'trip_id' => $newTripInstance->id,
                            'seat_id' => $inv->seat_id,
                            'booking_status' => $inv->booking_status,
                            'blocked_until' => $inv->blocked_until,
                            'booking_id' => $inv->booking_id,
                            'last_locked_user_id' => $inv->last_locked_user_id,
                            'created_by' => $inv->created_by,
                            'updated_by' => auth()->id(),
                        ]);
                    }

                    // Delete old records while the old trip still exists (partition resolution needs it)
                    SeatInventory::forTrip($tripInstance->id)->delete();
                    TripBoardingDropping::where('trip_id', $tripInstance->id)->delete();
                    $tripInstance->delete();
                    $tripInstance = $newTripInstance;
                } else {
                    $tripInstance = $this->applyUpdate($tripInstance, $attributes, $updateFields);
                }
            } else {
                $fieldsWithoutDate = array_diff($updateFields, ['trip_date']);
                $tripInstance = $this->applyUpdate($tripInstance, $attributes, $fieldsWithoutDate);
            }

            if (isset($attributes['boarding_dropping_points'])) {
                TripBoardingDropping::where('trip_id', $tripInstance->id)->delete();

                foreach ($attributes['boarding_dropping_points'] as $point) {
                    TripBoardingDropping::create([
                        'trip_id' => $tripInstance->id,
                        'counter_id' => $point['counter_id'],
                        'type' => $point['type'],
                        'time' => $point['time'],
                        'starting_point_status' => $point['starting_point_status'] ?? 0,
                        'ending_point_status' => $point['ending_point_status'] ?? 0,
                        'status' => $point['status'] ?? 1,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            return $tripInstance->fresh()->load(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'migratedTrip']);
        });
    }

    /**
     * Delete a trip instance and its boarding/dropping points.
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $tripInstance = $this->findById($id);
            TripBoardingDropping::where('trip_id', $tripInstance->id)->delete();
            $tripInstance->delete();
        });
    }

    /**
     * Migrate a trip instance to another trip.
     *
     * @throws ModelNotFoundException
     * @throws \RuntimeException
     */
    public function migrate(int $id, array $attributes): TripInstance
    {
        return DB::transaction(function () use ($id, $attributes) {
            $tripInstance = $this->findById($id);

            if ($tripInstance->isMigrated()) {
                throw new \RuntimeException('Trip instance is already migrated.');
            }

            $tripInstance->update([
                'status' => TripInstance::STATUS_MIGRATED,
                'migrated_trip_id' => $attributes['migrated_trip_id'],
                'migrated_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return $tripInstance->fresh();
        });
    }

    /**
     * Toggle the trip instance status between active and inactive.
     *
     * @throws ModelNotFoundException
     * @throws \RuntimeException
     */
    public function toggleStatus(int $id): TripInstance
    {
        return DB::transaction(function () use ($id) {
            $tripInstance = $this->findById($id);

            if ($tripInstance->isMigrated()) {
                throw new \RuntimeException('Cannot change status of a migrated trip instance.');
            }

            $tripInstance->update([
                'status' => $tripInstance->status === 1 ? 0 : 1,
                'updated_by' => auth()->id(),
            ]);

            return $tripInstance->fresh();
        });
    }

    /**
     * Set the trip instance status to active (1).
     *
     * @throws ModelNotFoundException
     * @throws \RuntimeException
     */
    public function activeById(int $id, ?string $hintDate = null): TripInstance
    {
        return DB::transaction(function () use ($id, $hintDate) {
            $tripInstance = $this->findById($id, $hintDate);

            if ($tripInstance->isMigrated()) {
                throw new \RuntimeException('Cannot change status of a migrated trip instance.');
            }

            $tripInstance->update(['status' => TripInstance::STATUS_ACTIVE, 'updated_by' => auth()->id()]);

            return $tripInstance->fresh();
        });
    }

    /**
     * Set the trip instance status to inactive (0).
     *
     * @throws ModelNotFoundException
     * @throws \RuntimeException
     */
    public function inactiveById(int $id, ?string $hintDate = null): TripInstance
    {
        return DB::transaction(function () use ($id, $hintDate) {
            $tripInstance = $this->findById($id, $hintDate);

            if ($tripInstance->isMigrated()) {
                throw new \RuntimeException('Cannot change status of a migrated trip instance.');
            }

            $tripInstance->update(['status' => TripInstance::STATUS_INACTIVE, 'updated_by' => auth()->id()]);

            return $tripInstance->fresh();
        });
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function paginateSinglePartition(array $attributes, ?Carbon $date, int $perPage): LengthAwarePaginator
    {
        // forDate()/forCurrentMonth() return a model instance with the correct partitioned
        // table set. newQuery() is required to get an actual Eloquent Builder so that
        // with(), where(), and paginate() all operate on the same builder chain.
        $baseModel = $date ? TripInstance::forDate($date) : TripInstance::forCurrentMonth();
        $query = $baseModel->newQuery()
            ->with(['coach', 'bus', 'schedule', 'seatPlan', 'route.startDistrict', 'route.endDistrict', 'driver', 'supervisor', 'migratedTrip']);

        foreach (['status', 'coach_type', 'coach_id', 'bus_id', 'schedule_id', 'route_id', 'driver_id', 'supervisor_id'] as $filter) {
            if (! empty($attributes[$filter])) {
                $query->where($filter, $attributes[$filter]);
            }
        }

        if (! empty($attributes['today'])) {
            $query->today();
        }

        if (! empty($attributes['upcoming'])) {
            $query->upcoming();
        }

        if (! empty($attributes['past'])) {
            $query->past();
        }

        $sortBy = $attributes['sort_by'] ?? 'trip_date';
        $sortOrder = $attributes['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder)->orderBy('id', 'desc');

        return $query->paginate($perPage);
    }

    private function paginateCrossPartition(array $attributes, Carbon $startDate, Carbon $endDate, int $perPage, int $page): LengthAwarePaginator
    {
        $rawQuery = (new TripInstance)->queryAcrossPartitions($startDate, $endDate);

        foreach (['status', 'coach_type', 'coach_id', 'bus_id', 'schedule_id', 'route_id', 'driver_id', 'supervisor_id'] as $filter) {
            if (! empty($attributes[$filter])) {
                $rawQuery->where($filter, $attributes[$filter]);
            }
        }

        $sortBy = $attributes['sort_by'] ?? 'trip_date';
        $sortOrder = $attributes['sort_order'] ?? 'desc';
        $rawQuery->orderBy($sortBy, $sortOrder)->orderBy('id', 'desc');

        $tripInstances = $rawQuery->get()->map(function ($item) {
            $model = new TripInstance;
            $model->setRawAttributes((array) $item, true);

            return $model;
        });

        $relatedData = $this->loadRelatedData($tripInstances);

        $tripInstances->each(function ($trip) use ($relatedData) {
            if ($relatedData['coaches']->has($trip->coach_id)) {
                $trip->setRelation('coach', $relatedData['coaches']->get($trip->coach_id));
            }
            if ($relatedData['buses']->has($trip->bus_id)) {
                $trip->setRelation('bus', $relatedData['buses']->get($trip->bus_id));
            }
            if ($relatedData['schedules']->has($trip->schedule_id)) {
                $trip->setRelation('schedule', $relatedData['schedules']->get($trip->schedule_id));
            }
            if ($relatedData['seat_plans']->has($trip->seat_plan_id)) {
                $trip->setRelation('seatPlan', $relatedData['seat_plans']->get($trip->seat_plan_id));
            }
            if ($relatedData['routes']->has($trip->route_id)) {
                $trip->setRelation('route', $relatedData['routes']->get($trip->route_id));
            }
            if ($trip->driver_id && $relatedData['employees']->has($trip->driver_id)) {
                $trip->setRelation('driver', $relatedData['employees']->get($trip->driver_id));
            }
            if ($trip->supervisor_id && $relatedData['employees']->has($trip->supervisor_id)) {
                $trip->setRelation('supervisor', $relatedData['employees']->get($trip->supervisor_id));
            }
        });

        $total = $tripInstances->count();
        $items = $tripInstances->forPage($page, $perPage)->values();

        return new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()]);
    }

    private function applyUpdate(TripInstance $tripInstance, array $attributes, array $fields): TripInstance
    {
        $data = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $attributes)) {
                $data[$field] = $attributes[$field];
            }
        }

        $data['updated_by'] = auth()->id();
        $tripInstance->update($data);

        return $tripInstance;
    }

    private function loadSeatInventory(int $tripId, TripInstance $tripInstance): array
    {
        $autoCreated = false;

        try {
            (new SeatInventory)->ensurePartitionExists($tripInstance->trip_date);

            $expiredInventoryIds = SeatInventory::forTrip($tripId)
                ->where('booking_status', SeatInventory::STATUS_BLOCKED)
                ->where('blocked_until', '<=', now())
                ->pluck('id');

            if ($expiredInventoryIds->isNotEmpty()) {
                SeatRequest::query()
                    ->whereIn('seat_inventory_id', $expiredInventoryIds)
                    ->where('status', 'pending')
                    ->update(['status' => 'expired']);

                SeatInventory::forTrip($tripId)
                    ->whereIn('id', $expiredInventoryIds)
                    ->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until' => null,
                        'last_locked_user_id' => null,
                    ]);
            }

            $seatInventories = SeatInventory::forTrip($tripId)
                ->with(['seat' => fn ($q) => $q->select('id', 'seat_plan_floor_id', 'seat_plan_id', 'seat_number', 'row_position', 'col_position', 'seat_type', 'is_disable')])
                ->get(['id', 'seat_id', 'booking_status', 'blocked_until', 'booking_id', 'last_locked_user_id']);

            if ($seatInventories->isEmpty()) {
                $result = $this->seatInventoryService->createSeatInventoryForTrip($tripId, $tripInstance->seat_plan_id);

                if ($result['success']) {
                    $autoCreated = true;
                    $seatInventories = SeatInventory::forTrip($tripId)
                        ->with(['seat' => fn ($q) => $q->select('id', 'seat_plan_floor_id', 'seat_plan_id', 'seat_number', 'row_position', 'col_position', 'seat_type', 'is_disable')])
                        ->get(['id', 'seat_id', 'booking_status', 'blocked_until', 'booking_id', 'last_locked_user_id']);
                }
            }

            $data = $seatInventories->map(fn ($inv) => [
                'id' => $inv->id,
                'seat_id' => $inv->seat_id,
                'booking_status' => $inv->booking_status,
                'blocked_until' => $inv->blocked_until,
                'booking_id' => $inv->booking_id,
                'last_locked_user_id' => $inv->last_locked_user_id,
                'seat_plan_floor_id' => $inv->seat->seat_plan_floor_id ?? null,
                'seat_number' => $inv->seat->seat_number ?? null,
                'row_position' => $inv->seat->row_position ?? null,
                'col_position' => $inv->seat->col_position ?? null,
                'seat_type' => $inv->seat->seat_type ?? null,
                'is_disable' => $inv->seat->is_disable ?? null,
            ])->toArray();

        } catch (\Exception $e) {
            Log::error("Failed to load seat inventory for trip {$tripId}: ".$e->getMessage());
            $data = [];
        }

        return ['data' => $data, 'auto_created' => $autoCreated];
    }

    private function formatFares(TripInstance $tripInstance): array
    {
        if (! $tripInstance->fares || $tripInstance->fares->isEmpty()) {
            return [];
        }

        return $tripInstance->fares->map(fn ($fare) => [
            'fare_id' => $fare->id,
            'seat_type' => $fare->seat_type,
            'amount' => $fare->amount ?? null,
            'coach_type' => $fare->coach_type,
            'coach_type_name' => $fare->coach_type_name,
            'route_id' => $fare->route_id,
            'seat_plan_id' => $fare->seat_plan_id,
            'status' => $fare->status,
            'status_name' => $fare->status_name,
            'from_date' => $fare->from_date?->format('Y-m-d H:i:s'),
            'to_date' => $fare->to_date?->format('Y-m-d H:i:s'),
            'created_by' => $fare->created_by,
            'updated_by' => $fare->updated_by,
            'created_at' => $fare->created_at,
            'updated_at' => $fare->updated_at,
        ])->toArray();
    }

    private function loadRelatedData(\Illuminate\Support\Collection $tripInstances): array
    {
        if ($tripInstances->isEmpty()) {
            return [
                'coaches' => collect(),
                'buses' => collect(),
                'schedules' => collect(),
                'seat_plans' => collect(),
                'routes' => collect(),
                'employees' => collect(),
            ];
        }

        $coachIds = $tripInstances->pluck('coach_id')->filter()->unique()->values()->toArray();
        $busIds = $tripInstances->pluck('bus_id')->filter()->unique()->values()->toArray();
        $scheduleIds = $tripInstances->pluck('schedule_id')->filter()->unique()->values()->toArray();
        $seatPlanIds = $tripInstances->pluck('seat_plan_id')->filter()->unique()->values()->toArray();
        $routeIds = $tripInstances->pluck('route_id')->filter()->unique()->values()->toArray();
        $employeeIds = $tripInstances
            ->flatMap(fn ($t) => [$t->driver_id, $t->supervisor_id])
            ->filter()->unique()->values()->toArray();

        return [
            'coaches' => Coach::whereIn('id', $coachIds)->get()->keyBy('id'),
            'buses' => Bus::whereIn('id', $busIds)->get()->keyBy('id'),
            'schedules' => Schedule::whereIn('id', $scheduleIds)->get()->keyBy('id'),
            'seat_plans' => SeatPlan::whereIn('id', $seatPlanIds)->get()->keyBy('id'),
            'routes' => TransportRoute::with(['startDistrict', 'endDistrict'])->whereIn('id', $routeIds)->get()->keyBy('id'),
            'employees' => Employee::whereIn('id', $employeeIds)->get()->keyBy('id'),
        ];
    }
}
