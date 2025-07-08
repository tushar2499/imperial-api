<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class SeatController extends Controller
{
    use ApiResponse;

    /**
     * Store new seats under an existing seat plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seat_plan_id' => 'required|exists:seat_plans,id', // Ensures the seat_plan_id exists
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

            // Prepare seat data for insertion
            $seats = array_map(function ($seat) use ($request) {
                return [
                    'seat_plan_id' => $request->seat_plan_id,
                    'seat_number' => $seat['seat_number'],
                    'row_position' => $seat['row_position'],
                    'col_position' => $seat['col_position'],
                    'seat_type' => $seat['seat_type'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->seats);

            // Insert seats into the seats table
            DB::table('seats')->insert($seats);

            DB::commit();

            return $this->successResponse($seats, 'Seats created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create seats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified seat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|string|max:10',
            'row_position' => 'required|integer|min:0',
            'col_position' => 'required|integer|min:0',
            'seat_type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update seat details
            $updated = DB::table('seats')->where('id', $id)->update([
                'seat_number' => $request->seat_number,
                'row_position' => $request->row_position,
                'col_position' => $request->col_position,
                'seat_type' => $request->seat_type,
                'updated_at' => now(),
            ]);

            if (!$updated) {
                return $this->errorResponse('Seat not found', 404);
            }

            // Get the updated seat details
            $seat = DB::table('seats')->where('id', $id)->first();
            DB::commit();

            return $this->successResponse($seat, 'Seat updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update seat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified seat.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete seat
            $deleted = DB::table('seats')->where('id', $id)->delete();

            if (!$deleted) {
                return $this->errorResponse('Seat not found', 404);
            }

            DB::commit();
            return $this->successResponse(null, 'Seat deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete seat: ' . $e->getMessage(), 500);
        }
    }
}
