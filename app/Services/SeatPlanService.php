<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\SeatPlan;
use App\Models\SeatPlanFloor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SeatPlanService
{
    /**
     * Get a paginated listing of seat plans with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = SeatPlan::withCount('seats');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active seat plans without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return SeatPlan::where('status', 1)->withCount('seats')->latest()->get();
    }

    /**
     * Create a new seat plan with floors and seats inside a database transaction.
     *
     * @param  array  $attributes
     * @return SeatPlan
     */
    public function store(array $attributes): SeatPlan
    {
        return DB::transaction(function () use ($attributes) {
            $seatPlan = SeatPlan::create([
                'name' => $attributes['name'],
                'floor' => $attributes['floor'],
                'status' => $attributes['status'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            foreach ($attributes['floors_data'] as $floorData) {
                $floor = SeatPlanFloor::create([
                    'seat_plan_id' => $seatPlan->id,
                    'name' => $floorData['name'],
                    'layout_type' => $floorData['layoutType'],
                    'rows' => $floorData['rows'],
                    'cols' => $floorData['cols'] ?? null,
                    'step' => $floorData['step'],
                    'is_extra_seat' => $floorData['extraSeat'],
                    'created_by' => auth()->id(),
                ]);

                foreach ($floorData['seats'] as $seatData) {
                    Seat::create([
                        'seat_plan_floor_id' => $floor->id,
                        'seat_plan_id' => $seatPlan->id,
                        'seat_number' => $seatData['seatName'] ?? null,
                        'row_position' => $seatData['rowNumber'],
                        'col_position' => $seatData['colNumber'],
                        'seat_type' => $seatData['seatType'] ?? null,
                        'is_disable' => $seatData['isDisable'],
                        'status' => $seatData['status'],
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            return $seatPlan->loadCount('seats')->load('floors.seats');
        });
    }

    /**
     * Find a seat plan by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return SeatPlan
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): SeatPlan
    {
        $seatPlan = SeatPlan::find($id);

        if (! $seatPlan) {
            throw new ModelNotFoundException("SeatPlan with ID {$id} not found.");
        }

        return $seatPlan;
    }

    /**
     * Find a seat plan with its floors, seats, and total seat count, or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return SeatPlan
     *
     * @throws ModelNotFoundException
     */
    public function findByIdWithFloors(int $id): SeatPlan
    {
        $seatPlan = SeatPlan::withCount('seats')->with('floors.seats')->find($id);

        if (! $seatPlan) {
            throw new ModelNotFoundException("SeatPlan with ID {$id} not found.");
        }

        return $seatPlan;
    }

    /**
     * Update an existing seat plan with its floors and seats inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return SeatPlan
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): SeatPlan
    {
        return DB::transaction(function () use ($id, $attributes) {
            $seatPlan = $this->findById($id);

            $seatPlan->update([
                'name' => $attributes['name'],
                'floor' => $attributes['floor'],
                'status' => $attributes['status'] ?? $seatPlan->status,
                'updated_by' => auth()->id(),
            ]);

            $keptFloorIds = [];
            $keptSeatIds = [];

            foreach ($attributes['floors_data'] as $floorData) {
                $floorId = isset($floorData['id']) && is_numeric($floorData['id'])
                    ? (int) $floorData['id']
                    : null;

                $floorPayload = [
                    'seat_plan_id' => $id,
                    'name' => $floorData['name'],
                    'layout_type' => $floorData['layoutType'],
                    'rows' => $floorData['rows'],
                    'cols' => $floorData['cols'] ?? null,
                    'step' => $floorData['step'],
                    'is_extra_seat' => $floorData['extraSeat'],
                    'updated_by' => auth()->id(),
                ];

                $floor = $floorId
                    ? SeatPlanFloor::where('id', $floorId)->where('seat_plan_id', $id)->first()
                    : null;

                if ($floor) {
                    $floor->update($floorPayload);
                } else {
                    $floor = SeatPlanFloor::create([...$floorPayload, 'created_by' => auth()->id()]);
                }

                $keptFloorIds[] = $floor->id;

                foreach ($floorData['seats'] as $seatData) {
                    $seatId = isset($seatData['id']) && is_numeric($seatData['id'])
                        ? (int) $seatData['id']
                        : null;

                    $seatPayload = [
                        'seat_plan_id' => $id,
                        'seat_plan_floor_id' => $floor->id,
                        'seat_number' => $seatData['seatName'] ?? null,
                        'row_position' => $seatData['rowNumber'],
                        'col_position' => $seatData['colNumber'],
                        'seat_type' => $seatData['seatType'] ?? null,
                        'is_disable' => $seatData['isDisable'],
                        'status' => $seatData['status'],
                        'updated_by' => auth()->id(),
                    ];

                    $seat = $seatId
                        ? Seat::where('id', $seatId)->where('seat_plan_floor_id', $floor->id)->first()
                        : null;

                    if ($seat) {
                        $seat->update($seatPayload);
                    } else {
                        $seat = Seat::create([...$seatPayload, 'created_by' => auth()->id()]);
                    }

                    $keptSeatIds[] = $seat->id;
                }
            }

            SeatPlanFloor::where('seat_plan_id', $id)->whereNotIn('id', $keptFloorIds)->delete();
            Seat::where('seat_plan_id', $id)->whereNotIn('id', $keptSeatIds)->delete();

            return $seatPlan->loadCount('seats')->load('floors.seats');
        });
    }

    /**
     * Set the seat plan status to active (1).
     *
     * @param  int  $id
     * @return SeatPlan
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): SeatPlan
    {
        return DB::transaction(function () use ($id) {
            $seatPlan = $this->findById($id);
            $seatPlan->update(['status' => 1]);

            return $seatPlan->fresh();
        });
    }

    /**
     * Set the seat plan status to inactive (0).
     *
     * @param  int  $id
     * @return SeatPlan
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): SeatPlan
    {
        return DB::transaction(function () use ($id) {
            $seatPlan = $this->findById($id);
            $seatPlan->update(['status' => 0]);

            return $seatPlan->fresh();
        });
    }

    /**
     * Delete the specified seat plan along with all its floors and seats inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $seatPlan = $this->findById($id);

            // Delete all seats first (covers orphan seats with null seat_plan_floor_id)
            Seat::where('seat_plan_id', $id)->delete();
            SeatPlanFloor::where('seat_plan_id', $id)->delete();
            $seatPlan->delete();
        });
    }
}
