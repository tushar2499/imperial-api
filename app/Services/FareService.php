<?php

namespace App\Services;

use App\Models\Fare;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FareService
{
    /**
     * Get a paginated listing of fares with optional search by route district names.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Fare::with(['route.startDistrict', 'route.endDistrict', 'seatPlan']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('route.startDistrict', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('route.endDistrict', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new fare inside a database transaction.
     *
     * @param  array  $attributes
     * @return Fare
     */
    public function store(array $attributes): Fare
    {
        return DB::transaction(function () use ($attributes) {
            $fare = Fare::create([
                'route_id' => $attributes['route_id'],
                'seat_plan_id' => $attributes['seat_plan_id'],
                'coach_type' => $attributes['coach_type'],
                'seat_type' => $attributes['seat_type'],
                'from_date' => $attributes['from_date'] ?? null,
                'to_date' => $attributes['to_date'] ?? null,
                'status' => $attributes['status'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            return $fare->load(['route.startDistrict', 'route.endDistrict', 'seatPlan']);
        });
    }

    /**
     * Find a fare by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Fare
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Fare
    {
        $fare = Fare::with(['route.startDistrict', 'route.endDistrict', 'seatPlan'])->find($id);

        if (! $fare) {
            throw new ModelNotFoundException("Fare with ID {$id} not found.");
        }

        return $fare;
    }

    /**
     * Update the specified fare inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Fare
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Fare
    {
        return DB::transaction(function () use ($id, $attributes) {
            $fare = $this->findById($id);

            $fare->update([
                'route_id' => $attributes['route_id'],
                'seat_plan_id' => $attributes['seat_plan_id'],
                'coach_type' => $attributes['coach_type'],
                'seat_type' => $attributes['seat_type'],
                'from_date' => $attributes['from_date'] ?? null,
                'to_date' => $attributes['to_date'] ?? null,
                'status' => $attributes['status'] ?? $fare->status,
                'updated_by' => auth()->id(),
            ]);

            return $fare->fresh(['route.startDistrict', 'route.endDistrict', 'seatPlan']);
        });
    }

    /**
     * Soft-delete the specified fare inside a database transaction.
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
