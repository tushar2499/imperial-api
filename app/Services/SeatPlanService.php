<?php

namespace App\Services;

use App\Models\SeatPlan;
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

        $query = SeatPlan::with('floors');

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
        return SeatPlan::where('status', 1)->latest()->get();
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
                $floor = DB::table('seat_plan_floors')->insertGetId([
                    'seat_plan_id' => $seatPlan->id,
                    'name' => $floorData['name'],
                    'layout_type' => $floorData['layoutType'],
                    'rows' => $floorData['rows'],
                    'cols' => $floorData['cols'] ?? null,
                    'step' => $floorData['step'],
                    'is_extra_seat' => $floorData['extraSeat'],
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($floorData['seats'] as $seat) {
                    DB::table('seats')->insert([
                        'seat_plan_floor_id' => $floor,
                        'seat_plan_id' => $seatPlan->id,
                        'seat_number' => $seat['seatName'] ?? null,
                        'row_position' => $seat['rowNumber'],
                        'col_position' => $seat['colNumber'],
                        'seat_type' => $seat['seatType'] ?? null,
                        'is_disable' => $seat['isDisable'],
                        'status' => $seat['status'],
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return $seatPlan->load('floors.seats');
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
     * Find a seat plan with its floors and seats, or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return SeatPlan
     *
     * @throws ModelNotFoundException
     */
    public function findByIdWithFloors(int $id): SeatPlan
    {
        $seatPlan = SeatPlan::with('floors.seats')->find($id);

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
                    'updated_at' => now(),
                ];

                if ($floorId && DB::table('seat_plan_floors')->where('id', $floorId)->exists()) {
                    DB::table('seat_plan_floors')->where('id', $floorId)->update($floorPayload);
                } else {
                    $floorPayload['created_by'] = auth()->id();
                    $floorPayload['created_at'] = now();
                    $floorId = DB::table('seat_plan_floors')->insertGetId($floorPayload);
                }

                $keptFloorIds[] = $floorId;

                foreach ($floorData['seats'] as $seat) {
                    $seatId = isset($seat['id']) && is_numeric($seat['id'])
                        ? (int) $seat['id']
                        : null;

                    $seatPayload = [
                        'seat_plan_id' => $id,
                        'seat_number' => $seat['seatName'] ?? null,
                        'row_position' => $seat['rowNumber'],
                        'col_position' => $seat['colNumber'],
                        'seat_type' => $seat['seatType'] ?? null,
                        'is_disable' => $seat['isDisable'],
                        'status' => $seat['status'],
                        'updated_by' => auth()->id(),
                        'updated_at' => now(),
                    ];

                    if ($seatId && DB::table('seats')->where('id', $seatId)->where('seat_plan_floor_id', $floorId)->exists()) {
                        DB::table('seats')->where('id', $seatId)->update($seatPayload);
                    } else {
                        $seatPayload['seat_plan_floor_id'] = $floorId;
                        $seatPayload['created_by'] = auth()->id();
                        $seatPayload['created_at'] = now();
                        $seatId = DB::table('seats')->insertGetId($seatPayload);
                    }

                    $keptSeatIds[] = $seatId;
                }
            }

            DB::table('seat_plan_floors')->where('seat_plan_id', $id)->whereNotIn('id', $keptFloorIds)->delete();
            DB::table('seats')->where('seat_plan_id', $id)->whereNotIn('id', $keptSeatIds)->delete();

            return $seatPlan->load('floors.seats');
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
     * Delete the specified seat plan along with its floors and seats inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->findById($id);

            DB::table('seats')->where('seat_plan_id', $id)->delete();
            DB::table('seat_plan_floors')->where('seat_plan_id', $id)->delete();
            DB::table('seat_plans')->where('id', $id)->delete();
        });
    }
}
