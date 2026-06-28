<?php

namespace App\Services;

use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StationService
{
    /**
     * Get a paginated listing of stations with optional search by district name.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Station::with('district');

        if ($search) {
            $query->whereHas('district', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create multiple station records (one per district ID) inside a transaction.
     *
     * @param  array  $attributes
     * @return Collection<int, Station>
     */
    public function store(array $attributes): Collection
    {
        return DB::transaction(function () use ($attributes) {
            $ids = [];

            foreach ($attributes['district_id'] as $districtId) {
                $station = Station::create([
                    'transport_route_id' => $attributes['transport_route_id'],
                    'district_id' => $districtId,
                    'status' => 'active',
                    'created_by' => auth()->id(),
                ]);

                $ids[] = $station->id;
            }

            return Station::with('district')->whereIn('id', $ids)->get();
        });
    }

    /**
     * Find a station by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Station
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Station
    {
        $station = Station::with('district')->find($id);

        if (! $station) {
            throw new ModelNotFoundException("Station with ID {$id} not found.");
        }

        return $station;
    }

    /**
     * Update the specified station inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Station
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Station
    {
        return DB::transaction(function () use ($id, $attributes) {
            $station = $this->findById($id);

            $station->update([
                'transport_route_id' => $attributes['transport_route_id'],
                'district_id' => $attributes['district_id'],
                'status' => $attributes['status'] ?? $station->status,
                'updated_by' => auth()->id(),
            ]);

            return $station->fresh(['district']);
        });
    }

    /**
     * Soft-delete the specified station inside a database transaction.
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
