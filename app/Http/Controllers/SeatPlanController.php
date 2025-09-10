<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeatPlanController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            DB::beginTransaction();

            // Get all seat plans
            $seatPlans = DB::table('seat_plans')->get();

            $seatPlanIds = $seatPlans->pluck('id');

            // Get all related seat floors
            $seatFloors = DB::table('seat_plan_floors')
                ->whereIn('seat_plan_id', $seatPlanIds)
                ->get();

            $seatFloorsGroupBySeatPlanId = $seatFloors->groupBy('seat_plan_id');

            // Get all related seats
            $seats = DB::table('seats')
                ->whereIn('seat_plan_id', $seatPlanIds)
                ->get();
            $seatsGroupBySeatPlanId           = $seats->groupBy('seat_plan_id');
            $seatsGroupBySeatPlanIdAndFloorId = $seats->groupBy(function ($seat) {
                return $seat->seat_plan_id . '-' . $seat->seat_plan_floor_id;
            });

            // Attach seats to each seat plan
            $seatPlansWithSeats = $seatPlans->map(function ($plan) use ($seatsGroupBySeatPlanId, $seatFloorsGroupBySeatPlanId, $seatsGroupBySeatPlanIdAndFloorId) {
                $plan->floorData = $seatFloorsGroupBySeatPlanId[$plan->id] ?? [];

                /*
                $plan->floorData = $plan->floorData->map(function ($floor) use ($seatsGroupBySeatPlanIdAndFloorId) {
                $floor->seats = $seatsGroupBySeatPlanIdAndFloorId[$floor->seat_plan_id . '-' . $floor->id] ?? [];
                return $floor;
                });
                $plan->seats = $seatsGroupBySeatPlanId[$plan->id] ?? [];
                 */

                return $plan;
            });

            DB::commit();

            return $this->successResponse($seatPlansWithSeats, 'Seat plans with seats retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve seat plans: ' . $e->getMessage(), 500);
        }

    }

    public function storeWithSeats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                            => 'required|string|max:255',
            'floor'                           => 'required|integer|in:1,2',
            'floors_data'                     => 'required|array|min:1',
            'floors_data.*.id'                => 'nullable|string|uuid',
            'floors_data.*.name'              => 'required|string|max:255',
            'floors_data.*.layoutType'        => 'required|string',
            'floors_data.*.rows'              => 'required|integer|min:1',
            'floors_data.*.cols'              => 'required|integer|min:1',
            'floors_data.*.step'              => 'required|integer|min:1',
            'floors_data.*.extraSeat'         => 'required|boolean',
            'floors_data.*.seats'             => 'required|array|min:1',
            'floors_data.*.seats.*.rowNumber' => 'required|integer|min:1',
            'floors_data.*.seats.*.colNumber' => 'required|integer|min:1',
            'floors_data.*.seats.*.seatName'  => 'nullable|string|max:255',
            'floors_data.*.seats.*.seatType'  => 'nullable|string|max:255',
            'floors_data.*.seats.*.isDisable' => 'required|integer|in:0,1',
            'floors_data.*.seats.*.status'    => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $seatPlanId = DB::table('seat_plans')->insertGetId([
                'name'       => $request->name,
                'floor'      => $request->floor,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->floors_data as $floor) {

                $seatPlanFloorId = DB::table('seat_plan_floors')->insertGetId([
                    'seat_plan_id'  => $seatPlanId,
                    'name'          => $floor['name'],
                    'layout_type'   => $floor['layoutType'],
                    'rows'          => $floor['rows'],
                    'cols'          => $floor['cols'] ?? null,
                    'step'          => $floor['step'],
                    'is_extra_seat' => $floor['extraSeat'],
                    'created_by'    => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                foreach ($floor['seats'] as $seat) {
                    DB::table('seats')->insertGetId([
                        'seat_plan_floor_id' => $seatPlanFloorId, // Now this will be the actual ID
                        'seat_plan_id'       => $seatPlanId,
                        'seat_number'        => $seat['seatName'] ?? null,
                        'row_position'       => $seat['rowNumber'],
                        'col_position'       => $seat['colNumber'],
                        'seat_type'          => $seat['seatType'] ?? null,
                        'is_disable'         => $seat['isDisable'],
                        'status'             => $seat['status'],
                        'created_by'         => auth()->id(),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }

            }

            DB::commit();

            $seatPlan         = DB::table('seat_plans')->where('id', $seatPlanId)->select('id', 'name', 'floor', 'created_by', 'updated_by', 'created_at', 'updated_at')->first();
            $seatPlan->floors = DB::table('seat_plan_floors')->where('seat_plan_id', $seatPlanId)->get();

            foreach ($seatPlan->floors as $floor) {
                $floor->seats = DB::table('seats')->where('seat_plan_id', $seatPlanId)->get();
            }

            return $this->successResponse($seatPlan, 'Seat plan and seats created successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create seat plan with seats: ' . $e->getMessage(), 500);
        }

    }

    public function show($id)
    {
        try {
            DB::beginTransaction();

            // Fetch the seat plan
            $plan = DB::table('seat_plans')->where('id', $id)->select('id', 'name', 'floor', 'created_by', 'updated_by', 'created_at', 'updated_at')->first();

            if (!$plan) {
                DB::rollBack();

                return $this->errorResponse('Seat plan not found', 404);
            }

            // Fetch floors associated with the seat plan
            $floors = DB::table('seat_plan_floors')->where('seat_plan_id', $id)->get();

            // Attach floors to the plan
            $plan->floors = $floors;

            foreach ($plan->floors as $floor) {
                $floor->seats = DB::table('seats')->where('seat_plan_floor_id', $floor->id)->get();
            }

            DB::commit();

            return $this->successResponse($plan, 'Seat plan with seats retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve seat plan: ' . $e->getMessage(), 500);
        }

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'                            => 'required|string|max:255',
            'floor'                           => 'required|integer|in:1,2',
            'floors_data'                     => 'required|array|min:1',
            'floors_data.*.id'                => 'nullable',
            'floors_data.*.name'              => 'required|string|max:255',
            'floors_data.*.layoutType'        => 'required|string',
            'floors_data.*.rows'              => 'required|integer|min:1',
            'floors_data.*.cols'              => 'required|integer|min:1',
            'floors_data.*.step'              => 'required|integer|min:1',
            'floors_data.*.extraSeat'         => 'required|boolean',
            'floors_data.*.seats'             => 'required|array|min:1',
            'floors_data.*.seats.*.id'        => 'nullable',
            'floors_data.*.seats.*.rowNumber' => 'required|integer|min:1',
            'floors_data.*.seats.*.colNumber' => 'required|integer|min:1',
            'floors_data.*.seats.*.seatName'  => 'nullable|string|max:255',
            'floors_data.*.seats.*.seatType'  => 'nullable|string|max:255',
            'floors_data.*.seats.*.isDisable' => 'required|integer|in:0,1',
            'floors_data.*.seats.*.status'    => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();
            $updated = DB::table('seat_plans')->where('id', $id)->update([
                'name'       => $request->name,
                'floor'      => $request->floor,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            if (!$updated) {
                return $this->errorResponse('Seat plan not found', 404);
            }

            $existingSeatPlaneFloorIds = [];
            $existingSeatIds           = [];

            foreach ($request->floors_data as $floor) {

                $seatPlanFloorId = isset($floor['id']) && is_numeric($floor['id']) ? floatval($floor['id']) : null;

                $seatPlaneFloorData = [
                    'seat_plan_id'  => $id,
                    'name'          => $floor['name'],
                    'layout_type'   => $floor['layoutType'],
                    'rows'          => $floor['rows'],
                    'cols'          => $floor['cols'] ?? null,
                    'step'          => $floor['step'],
                    'is_extra_seat' => $floor['extraSeat'],
                    'created_by'    => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                if ($seatPlanFloorId) {
                    $seatPlaneFloor = DB::table('seat_plan_floors')->where('id', $seatPlanFloorId)->first();

                    if ($seatPlaneFloor) {
                        $updated = DB::table('seat_plan_floors')->where('id', $id)->update($seatPlaneFloorData);
                    } else {
                        $seatPlanFloorId = DB::table('seat_plan_floors')->insertGetId($seatPlaneFloorData);
                    }

                } else {
                    $seatPlanFloorId = DB::table('seat_plan_floors')->insertGetId($seatPlaneFloorData);
                }

                $existingSeatPlaneFloorIds[] = $seatPlanFloorId;

                foreach ($floor['seats'] as $seat) {
                    $seatId = isset($seat['id']) && is_numeric($seat['id']) ? floatval($seat['id']) : null;

                    $seatData = [
                        'seat_plan_id'       => $id,
                        'seat_number'        => $seat['seatName'] ?? null,
                        'row_position'       => $seat['rowNumber'],
                        'col_position'       => $seat['colNumber'],
                        'seat_type'          => $seat['seatType'] ?? null,
                        'is_disable'         => $seat['isDisable'],
                        'status'             => $seat['status'],
                        'created_by'         => auth()->id(),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];

                    if ($seatId) {
                        $seatPlaneFloor = DB::table('seats')
                            ->where('id', $seatId)
                            ->where('seat_plan_floor_id', $seatPlanFloorId)
                            ->first();

                        if ($seatPlaneFloor) {
                            $updated = DB::table('seats')->where('id', $seatId)->update($seatData);
                        } else {
                            $seatData['seat_plan_floor_id'] = $seatPlanFloorId;
                            $seatId                         = DB::table('seats')->insertGetId($seatData);
                        }

                    } else {
                        $seatData['seat_plan_floor_id'] = $seatPlanFloorId;
                        $seatId                         = DB::table('seats')->insertGetId($seatData);
                    }

                    $existingSeatIds[] = $seatId;

                }

            }

            /**
             * Delete the seat plan floors and seats that are not in the request
             */
            DB::table('seat_plan_floors')->where('seat_plan_id', $id)->whereNotIn('id', $existingSeatPlaneFloorIds)->delete();
            DB::table('seats')->where('seat_plan_id', $id)->whereNotIn('id', $existingSeatIds)->delete();

            DB::commit();

            $seatPlan         = DB::table('seat_plans')->where('id', $id)->select('id', 'name', 'floor', 'created_by', 'updated_by', 'created_at', 'updated_at')->first();
            $seatPlan->floors = DB::table('seat_plan_floors')->where('seat_plan_id', $id)->get();

            foreach ($seatPlan->floors as $floor) {
                $floor->seats = DB::table('seats')->where('seat_plan_id', $id)->get();
            }

            return $this->successResponse($seatPlan, 'Seat plan updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update seat plan: ' . $e->getMessage(), 500);
        }

    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $plan = DB::table('seat_plans')->where('id', $id)->first();

            if (!$plan) {
                DB::rollBack();

                return $this->errorResponse('Seat plan not found', 404);
            }

            DB::table('seat_plans')->where('id', $id)->delete();
            DB::table('seat_plan_floors')->where('seat_plan_id', $id)->delete();
            DB::table('seats')->where('seat_plan_id', $id)->delete();

            DB::commit();

            return $this->successResponse(null, 'Seat plan deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete seat plan: ' . $e->getMessage(), 500);
        }

    }

}
