<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DistrictService
{
    /**
     * Get a paginated listing of districts with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = District::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active districts without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return District::where('status', 1)->latest()->get();
    }

    /**
     * Create a new district inside a database transaction.
     *
     * @param  array  $attributes
     * @return District
     */
    public function store(array $attributes): District
    {
        return DB::transaction(fn () => District::create([
            'name' => $attributes['name'],
            'code' => $attributes['code'] ?? null,
            'status' => $attributes['status'] ?? 1,
        ]));
    }

    /**
     * Find a district by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return District
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): District
    {
        $district = District::find($id);

        if (! $district) {
            throw new ModelNotFoundException("District with ID {$id} not found.");
        }

        return $district;
    }

    /**
     * Update the specified district inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return District
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): District
    {
        return DB::transaction(function () use ($id, $attributes) {
            $district = $this->findById($id);

            $district->update([
                'name' => $attributes['name'],
                'code' => $attributes['code'] ?? $district->code,
                'status' => $attributes['status'] ?? $district->status,
            ]);

            return $district->fresh();
        });
    }

    /**
     * Soft-delete the specified district inside a database transaction.
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
