<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SeatController extends Controller
{
    use ApiResponse;

    /**
     * Store new seats under an existing seat plan floor.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'seat_plan_id' => ['required', 'integer', 'exists:seat_plans,id'],
            'seat_plan_floor_id' => [
                'nullable',
                'integer',
                Rule::exists('seat_plan_floors', 'id')->where('seat_plan_id', $request->input('seat_plan_id')),
            ],
            'seats' => ['required', 'array', 'min:1'],
            'seats.*.seat_number' => ['nullable', 'string', 'max:10'],
            'seats.*.row_position' => ['required', 'integer', 'min:0'],
            'seats.*.col_position' => ['required', 'integer', 'min:0'],
            'seats.*.seat_type' => ['nullable', 'string', 'max:50'],
            'seats.*.is_disable' => ['nullable', 'boolean'],
            'seats.*.status' => ['nullable', 'integer', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return $this->validationFailedResponse($validator->errors()->toArray());
        }

        return DB::transaction(function () use ($request) {
            $createdSeats = [];

            foreach ($request->seats as $seatData) {
                $createdSeats[] = Seat::create([
                    'seat_plan_id' => $request->seat_plan_id,
                    'seat_plan_floor_id' => $request->seat_plan_floor_id ?? null,
                    'seat_number' => $seatData['seat_number'] ?? null,
                    'row_position' => $seatData['row_position'],
                    'col_position' => $seatData['col_position'],
                    'seat_type' => $seatData['seat_type'] ?? null,
                    'is_disable' => $seatData['is_disable'] ?? false,
                    'status' => $seatData['status'] ?? 1,
                    'created_by' => auth()->id(),
                ]);
            }

            return $this->createdResponse($createdSeats, 'Seats created successfully');
        });
    }

    /**
     * Update the specified seat.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => ['nullable', 'string', 'max:10'],
            'row_position' => ['required', 'integer', 'min:0'],
            'col_position' => ['required', 'integer', 'min:0'],
            'seat_type' => ['nullable', 'string', 'max:50'],
            'is_disable' => ['nullable', 'boolean'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return $this->validationFailedResponse($validator->errors()->toArray());
        }

        $seat = Seat::find($id);

        if (! $seat) {
            return $this->notFoundResponse('Seat not found');
        }

        $seat->update([
            'seat_number' => $request->seat_number,
            'row_position' => $request->row_position,
            'col_position' => $request->col_position,
            'seat_type' => $request->seat_type,
            'is_disable' => $request->input('is_disable', $seat->is_disable),
            'status' => $request->input('status', $seat->status),
            'updated_by' => auth()->id(),
        ]);

        return $this->successResponse($seat->fresh(), 'Seat updated successfully');
    }

    /**
     * Remove the specified seat.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $seat = Seat::find($id);

        if (! $seat) {
            return $this->notFoundResponse('Seat not found');
        }

        $seat->delete();

        return $this->successResponse(null, 'Seat deleted successfully');
    }
}
