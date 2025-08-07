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

            // Get all related seats
            $seatPlanIds = $seatPlans->pluck('id');
            $seats       = DB::table('seats')
                ->whereIn('seat_plan_id', $seatPlanIds)
                ->get()
                ->groupBy('seat_plan_id');

            // Attach seats to each seat plan
            $seatPlansWithSeats = $seatPlans->map(function ($plan) use ($seats) {
                $plan->seats = $seats[$plan->id] ?? [];

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
            'floor'                           => 'required|string',
            'floors_data'                     => 'required|array|min:1',
            'floors_data.*.id'                => 'required|string|uuid',
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
                    DB::table('seats')->insert([
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

            $seatPlan = DB::table('seat_plans')->where('id', $seatPlanId)->first();
            $seatList = DB::table('seats')->where('seat_plan_id', $seatPlanId)->get();

            return $this->successResponse([
                'seat_plan' => $seatPlan,
                'seats'     => $seatList,
            ], 'Seat plan and seats created successfully', 201);

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
            $plan = DB::table('seat_plans')->where('id', $id)->first();

            if (!$plan) {
                DB::rollBack();

                return $this->errorResponse('Seat plan not found', 404);
            }

            // Fetch seats associated with the seat plan
            $seats = DB::table('seats')->where('seat_plan_id', $id)->get();

            // Attach seats to the plan
            $plan->seats = $seats;

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
            'name'        => 'required|string|max:255',
            'rows'        => 'required|integer|min:1',
            'cols'        => 'required|integer|min:1',
            'layout_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();
            $updated = DB::table('seat_plans')->where('id', $id)->update([
                'name'        => $request->name,
                'rows'        => $request->rows,
                'cols'        => $request->cols,
                'layout_type' => $request->layout_type,
                'updated_by'  => auth()->id(),
                'updated_at'  => now(),
            ]);

            if (!$updated) {
                return $this->errorResponse('Seat plan not found', 404);
            }

            $plan = DB::table('seat_plans')->where('id', $id)->first();
            DB::commit();

            return $this->successResponse($plan, 'Seat plan updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update seat plan: ' . $e->getMessage(), 500);
        }

    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $deleted = DB::table('seat_plans')->where('id', $id)->delete();

            if (!$deleted) {
                return $this->errorResponse('Seat plan not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Seat plan deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete seat plan: ' . $e->getMessage(), 500);
        }

    }

}
