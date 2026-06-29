<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\CoachBoardingDropping;
use App\Models\CoachConfiguration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CoachConfigurationService
{
    private const LIST_RELATIONS = [
        'coach',
        'schedule',
        'bus',
        'seatPlan',
        'transportRoute.startDistrict',
        'transportRoute.endDistrict',
    ];

    private const DETAIL_RELATIONS = [
        'coach',
        'schedule',
        'bus',
        'seatPlan',
        'transportRoute.startDistrict',
        'transportRoute.endDistrict',
        'boardingDroppings.counter',
    ];

    /**
     * Get a paginated listing of coach configurations with optional filters.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;

        $query = CoachConfiguration::with(self::LIST_RELATIONS)
            ->with(['seatPlan' => fn ($q) => $q->withCount('seats')]);

        if (isset($attributes['status'])) {
            $query->where('status', $attributes['status']);
        }

        if (isset($attributes['coach_type'])) {
            $query->where('coach_type', $attributes['coach_type']);
        }

        if (isset($attributes['coach_id'])) {
            $query->where('coach_id', $attributes['coach_id']);
        }

        if (isset($attributes['schedule_id'])) {
            $query->where('schedule_id', $attributes['schedule_id']);
        }

        if (isset($attributes['transport_route_id'])) {
            $query->where('transport_route_id', $attributes['transport_route_id']);
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active coach configurations without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return CoachConfiguration::with(self::LIST_RELATIONS)
            ->with(['seatPlan' => fn ($q) => $q->withCount('seats')])
            ->where('status', CoachConfiguration::STATUS_ACTIVE)
            ->latest()
            ->get();
    }

    /**
     * Create a new coach configuration with boarding/dropping points inside a transaction.
     *
     * @param  array  $attributes
     * @return CoachConfiguration
     */
    public function store(array $attributes): CoachConfiguration
    {
        return DB::transaction(function () use ($attributes) {
            $coach = Coach::findOrFail($attributes['coach_id']);

            $coachConfiguration = CoachConfiguration::create([
                'coach_id' => $attributes['coach_id'],
                'schedule_id' => $attributes['schedule_id'],
                'bus_id' => $attributes['bus_id'],
                'seat_plan_id' => $coach->seat_plan_id,
                'transport_route_id' => $attributes['transport_route_id'],
                'coach_type' => $coach->coach_type,
                'status' => $attributes['status'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            foreach ($attributes['boarding_dropping_points'] as $point) {
                CoachBoardingDropping::create([
                    'coach_configuration_id' => $coachConfiguration->id,
                    'counter_id' => $point['counter_id'],
                    'type' => $point['type'],
                    'time' => $point['time'],
                    'starting_point_status' => $point['starting_point_status'] ?? 0,
                    'ending_point_status' => $point['ending_point_status'] ?? 0,
                    'status' => $point['status'] ?? 1,
                    'created_by' => auth()->id(),
                ]);
            }

            return $coachConfiguration->load(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Find a coach configuration by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return CoachConfiguration
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): CoachConfiguration
    {
        $coachConfiguration = CoachConfiguration::with(self::DETAIL_RELATIONS)->find($id);

        if (! $coachConfiguration) {
            throw new ModelNotFoundException("Coach configuration with ID {$id} not found.");
        }

        $coachConfiguration->seatPlan?->loadCount('seats');

        return $coachConfiguration;
    }

    /**
     * Update the specified coach configuration with boarding/dropping points inside a transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return CoachConfiguration
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): CoachConfiguration
    {
        return DB::transaction(function () use ($id, $attributes) {
            $coachConfiguration = $this->findById($id);

            $coachConfiguration->update([
                'coach_id' => $attributes['coach_id'],
                'schedule_id' => $attributes['schedule_id'],
                'bus_id' => $attributes['bus_id'],
                'seat_plan_id' => $attributes['seat_plan_id'],
                'transport_route_id' => $attributes['transport_route_id'],
                'coach_type' => $attributes['coach_type'],
                'status' => $attributes['status'] ?? $coachConfiguration->status,
                'updated_by' => auth()->id(),
            ]);

            CoachBoardingDropping::where('coach_configuration_id', $coachConfiguration->id)->delete();

            foreach ($attributes['boarding_dropping_points'] as $point) {
                CoachBoardingDropping::create([
                    'coach_configuration_id' => $coachConfiguration->id,
                    'counter_id' => $point['counter_id'],
                    'type' => $point['type'],
                    'time' => $point['time'],
                    'starting_point_status' => $point['starting_point_status'] ?? 0,
                    'ending_point_status' => $point['ending_point_status'] ?? 0,
                    'status' => $point['status'] ?? 1,
                    'created_by' => auth()->id(),
                ]);
            }

            return $coachConfiguration->fresh()->load(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Set the coach configuration status to active (1).
     *
     * @param  int  $id
     * @return CoachConfiguration
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): CoachConfiguration
    {
        return DB::transaction(function () use ($id) {
            $coachConfiguration = $this->findById($id);
            $coachConfiguration->update([
                'status' => CoachConfiguration::STATUS_ACTIVE,
                'updated_by' => auth()->id(),
            ]);

            return $coachConfiguration->fresh();
        });
    }

    /**
     * Set the coach configuration status to inactive (0).
     *
     * @param  int  $id
     * @return CoachConfiguration
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): CoachConfiguration
    {
        return DB::transaction(function () use ($id) {
            $coachConfiguration = $this->findById($id);
            $coachConfiguration->update([
                'status' => CoachConfiguration::STATUS_INACTIVE,
                'updated_by' => auth()->id(),
            ]);

            return $coachConfiguration->fresh();
        });
    }

    /**
     * Delete the specified coach configuration and its boarding/dropping points inside a transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $coachConfiguration = $this->findById($id);
            CoachBoardingDropping::where('coach_configuration_id', $coachConfiguration->id)->delete();
            $coachConfiguration->delete();
        });
    }

    /**
     * Get coach configurations filtered by schedule ID.
     *
     * @param  int  $scheduleId
     * @return Collection
     */
    public function getBySchedule(int $scheduleId): Collection
    {
        return CoachConfiguration::with([
            'coach',
            'bus',
            'seatPlan',
            'transportRoute',
            'boardingDroppings.counter',
        ])->where('schedule_id', $scheduleId)->get();
    }

    /**
     * Get coach configurations filtered by coach ID.
     *
     * @param  int  $coachId
     * @return Collection
     */
    public function getByCoach(int $coachId): Collection
    {
        return CoachConfiguration::with([
            'schedule',
            'bus',
            'seatPlan',
            'transportRoute',
            'boardingDroppings.counter',
        ])->where('coach_id', $coachId)->get();
    }

    /**
     * Get coach configurations filtered by route ID.
     *
     * @param  int  $routeId
     * @return Collection
     */
    public function getByRoute(int $routeId): Collection
    {
        return CoachConfiguration::with([
            'coach',
            'schedule',
            'bus',
            'seatPlan',
            'boardingDroppings.counter',
        ])->where('transport_route_id', $routeId)->get();
    }
}
