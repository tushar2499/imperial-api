<?php

namespace App\Services;

use App\Models\Fare;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
     * Get all active fares without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return Fare::with(['route.startDistrict', 'route.endDistrict', 'seatPlan'])
            ->where('status', Fare::STATUS_ACTIVE)
            ->latest()
            ->get();
    }

    /**
     * Set the fare status to active (1).
     *
     * @param  int  $id
     * @return Fare
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): Fare
    {
        return DB::transaction(function () use ($id) {
            $fare = $this->findById($id);
            $fare->update(['status' => Fare::STATUS_ACTIVE]);

            return $fare->fresh(['route.startDistrict', 'route.endDistrict', 'seatPlan']);
        });
    }

    /**
     * Set the fare status to inactive (0).
     *
     * @param  int  $id
     * @return Fare
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): Fare
    {
        return DB::transaction(function () use ($id) {
            $fare = $this->findById($id);
            $fare->update(['status' => Fare::STATUS_INACTIVE]);

            return $fare->fresh(['route.startDistrict', 'route.endDistrict', 'seatPlan']);
        });
    }

    /**
     * Create multiple fares (one per seat type) inside a database transaction.
     *
     * @param  array  $attributes
     * @return Collection
     */
    public function store(array $attributes): Collection
    {
        return DB::transaction(function () use ($attributes) {
            $fares = new Collection;
            $fromDate = $attributes['from_date'] ?? null;
            $toDate = $attributes['to_date'] ?? null;

            foreach ($attributes['seat_types'] as $seatType => $amount) {
                $this->ensureNoDuplicate(
                    $attributes['route_id'],
                    $attributes['seat_plan_id'],
                    $attributes['coach_type'],
                    $seatType,
                    $fromDate,
                    $toDate
                );

                $fare = Fare::create([
                    'route_id' => $attributes['route_id'],
                    'seat_plan_id' => $attributes['seat_plan_id'],
                    'coach_type' => $attributes['coach_type'],
                    'seat_type' => $seatType,
                    'amount' => $amount,
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'status' => $attributes['status'] ?? 1,
                    'created_by' => auth()->id(),
                ]);

                $fares->push($fare->load(['route.startDistrict', 'route.endDistrict', 'seatPlan']));
            }

            return $fares;
        });
    }

    /**
     * Throw a ValidationException if a duplicate fare exists for the given combination.
     *
     * Case 1 — both dates provided: reject if any existing fare overlaps the date range.
     * Case 2 — either date is null: reject if any existing fare with a null date already exists.
     */
    private function ensureNoDuplicate(
        int $routeId,
        int $seatPlanId,
        int $coachType,
        string $seatType,
        ?string $fromDate,
        ?string $toDate,
        ?int $excludeId = null
    ): void {
        $base = Fare::where('route_id', $routeId)
            ->where('seat_plan_id', $seatPlanId)
            ->where('coach_type', $coachType)
            ->where('seat_type', $seatType)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId));

        if ($fromDate !== null && $toDate !== null) {
            $exists = (clone $base)
                ->whereNotNull('from_date')
                ->whereNotNull('to_date')
                ->where('from_date', '<=', $toDate)
                ->where('to_date', '>=', $fromDate)
                ->exists();
        } else {
            $exists = (clone $base)
                ->where(function ($q) {
                    $q->whereNull('from_date')->orWhereNull('to_date');
                })
                ->exists();
        }

        if ($exists) {
            if ($fromDate !== null && $toDate !== null) {
                throw ValidationException::withMessages([
                    'seat_types' => "A fare for seat type '{$seatType}' already exists for this route and date range.",
                ]);
            } else {
                throw ValidationException::withMessages([
                    'seat_type' => "A fare for seat type '{$seatType}' already exists for this route and date range",
                ]);
            }
        }
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

            $this->ensureNoDuplicate(
                $attributes['route_id'],
                $attributes['seat_plan_id'],
                $attributes['coach_type'],
                $attributes['seat_type'],
                $attributes['from_date'] ?? null,
                $attributes['to_date'] ?? null,
                $id
            );

            $fare->update([
                'route_id' => $attributes['route_id'],
                'seat_plan_id' => $attributes['seat_plan_id'],
                'coach_type' => $attributes['coach_type'],
                'seat_type' => $attributes['seat_type'],
                'amount' => $attributes['amount'],
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
