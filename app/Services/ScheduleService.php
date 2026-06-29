<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Get a paginated listing of schedules with optional search by name.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Schedule::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active schedules without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return Schedule::where('status', 1)->latest()->get();
    }

    /**
     * Create a new schedule inside a database transaction.
     *
     * @param  array  $attributes
     * @return Schedule
     */
    public function store(array $attributes): Schedule
    {
        return DB::transaction(fn () => Schedule::create([
            'name' => $attributes['name'],
            'status' => $attributes['status'] ?? 1,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a schedule by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Schedule
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Schedule
    {
        $schedule = Schedule::find($id);

        if (! $schedule) {
            throw new ModelNotFoundException("Schedule with ID {$id} not found.");
        }

        return $schedule;
    }

    /**
     * Update the specified schedule inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Schedule
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Schedule
    {
        return DB::transaction(function () use ($id, $attributes) {
            $schedule = $this->findById($id);

            $schedule->update([
                'name' => $attributes['name'],
                'status' => $attributes['status'] ?? $schedule->status,
                'updated_by' => auth()->id(),
            ]);

            return $schedule->fresh();
        });
    }

    /**
     * Set the schedule status to active (1).
     *
     * @param  int  $id
     * @return Schedule
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): Schedule
    {
        return DB::transaction(function () use ($id) {
            $schedule = $this->findById($id);
            $schedule->update(['status' => 1]);

            return $schedule->fresh();
        });
    }

    /**
     * Set the schedule status to inactive (0).
     *
     * @param  int  $id
     * @return Schedule
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): Schedule
    {
        return DB::transaction(function () use ($id) {
            $schedule = $this->findById($id);
            $schedule->update(['status' => 0]);

            return $schedule->fresh();
        });
    }

    /**
     * Soft-delete the specified schedule inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->findById($id)->delete();
        });
    }
}
