<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

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
            $seats = DB::table('seats')
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
            'name' => 'required|string|max:255',
            'rows' => 'required|integer|min:1',
            'cols' => 'required|integer|min:1',
            'layout_type' => 'nullable|string|max:100',
            'seats' => 'required|array|min:1',
            'seats.*.seat_number' => 'required|string|max:10',
            'seats.*.row_position' => 'required|integer|min:0',
            'seats.*.col_position' => 'required|integer|min:0',
            'seats.*.seat_type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $seatPlanId = DB::table('seat_plans')->insertGetId([
                'name' => $request->name,
                'rows' => $request->rows,
                'cols' => $request->cols,
                'layout_type' => $request->layout_type,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $seats = array_map(function ($seat) use ($seatPlanId) {
                return [
                    'seat_plan_id' => $seatPlanId,
                    'seat_number' => $seat['seat_number'],
                    'row_position' => $seat['row_position'],
                    'col_position' => $seat['col_position'],
                    'seat_type' => $seat['seat_type'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->seats);

            DB::table('seats')->insert($seats);

            DB::commit();

            $seatPlan = DB::table('seat_plans')->where('id', $seatPlanId)->first();
            $seatList = DB::table('seats')->where('seat_plan_id', $seatPlanId)->get();

            return $this->successResponse([
                'seat_plan' => $seatPlan,
                'seats' => $seatList
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
            $plan = DB::table('seat_plans')->where('id', $id)->first();
            if (!$plan) return $this->errorResponse('Seat plan not found', 404);
            DB::commit();
            return $this->successResponse($plan, 'Seat plan retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve seat plan: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rows' => 'required|integer|min:1',
            'cols' => 'required|integer|min:1',
            'layout_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();
            $updated = DB::table('seat_plans')->where('id', $id)->update([
                'name' => $request->name,
                'rows' => $request->rows,
                'cols' => $request->cols,
                'layout_type' => $request->layout_type,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            if (!$updated) return $this->errorResponse('Seat plan not found', 404);

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
            if (!$deleted) return $this->errorResponse('Seat plan not found', 404);
            DB::commit();
            return $this->successResponse(null, 'Seat plan deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to delete seat plan: ' . $e->getMessage(), 500);
        }
    }
}
