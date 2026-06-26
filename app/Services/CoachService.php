<?php

namespace App\Services;

use App\Models\Coach;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CoachService
{
    /**
     * Get a paginated listing of coaches with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Coach::query();

        if ($search) {
            $query->where('coach_no', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new coach inside a database transaction.
     *
     * @param  array  $attributes
     * @return Coach
     */
    public function store(array $attributes): Coach
    {
        return DB::transaction(fn () => Coach::create([
            'coach_no' => $attributes['coach_no'],
            'seat_plan_id' => $attributes['seat_plan_id'],
            'coach_type' => $attributes['coach_type'],
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a coach by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Coach
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Coach
    {
        $coach = Coach::find($id);

        if (! $coach) {
            throw new ModelNotFoundException("Coach with ID {$id} not found.");
        }

        return $coach;
    }

    /**
     * Update the specified coach inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Coach
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Coach
    {
        return DB::transaction(function () use ($id, $attributes) {
            $coach = $this->findById($id);

            $coach->update([
                'coach_no' => $attributes['coach_no'],
                'seat_plan_id' => $attributes['seat_plan_id'],
                'coach_type' => $attributes['coach_type'],
                'updated_by' => auth()->id(),
            ]);

            return $coach->fresh();
        });
    }

    /**
     * Soft-delete the specified coach inside a database transaction.
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
