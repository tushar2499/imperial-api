<?php

namespace App\Services;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DesignationService
{
    /**
     * Get a paginated listing of designations with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Designation::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active designations without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return Designation::where('status', 1)->latest()->get();
    }

    /**
     * Create a new designation inside a database transaction.
     *
     * @param  array  $attributes
     * @return Designation
     */
    public function store(array $attributes): Designation
    {
        return DB::transaction(fn () => Designation::create([
            'name' => $attributes['name'],
            'status' => $attributes['status'] ?? 1,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a designation by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Designation
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Designation
    {
        $designation = Designation::find($id);

        if (! $designation) {
            throw new ModelNotFoundException("Designation with ID {$id} not found.");
        }

        return $designation;
    }

    /**
     * Update the specified designation inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Designation
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Designation
    {
        return DB::transaction(function () use ($id, $attributes) {
            $designation = $this->findById($id);

            $designation->update([
                'name' => $attributes['name'],
                'status' => $attributes['status'] ?? $designation->status,
                'updated_by' => auth()->id(),
            ]);

            return $designation->fresh();
        });
    }

    /**
     * Set the designation status to active (1).
     *
     * @param  int  $id
     * @return Designation
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): Designation
    {
        return DB::transaction(function () use ($id) {
            $designation = $this->findById($id);
            $designation->update(['status' => 1]);

            return $designation->fresh();
        });
    }

    /**
     * Set the designation status to inactive (0).
     *
     * @param  int  $id
     * @return Designation
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): Designation
    {
        return DB::transaction(function () use ($id) {
            $designation = $this->findById($id);
            $designation->update(['status' => 0]);

            return $designation->fresh();
        });
    }

    /**
     * Soft-delete the specified designation inside a database transaction.
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
