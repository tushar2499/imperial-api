<?php

namespace App\Http\Controllers;

use App\Models\TripInstance;
use App\Services\SeatInventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripInstanceSeatInventoryController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SeatInventoryService $seatInventoryService) {}

    public function getSeatInventory(int $id, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_status' => 'sometimes|in:0,1,2,3',
                'seat_type' => 'sometimes|string',
                'include_seat_details' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (! $tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            $filters = $request->only(['booking_status', 'seat_type']);
            $result = $this->seatInventoryService->getTripSeatInventory($id, $filters);

            if (! $result['success']) {
                return $this->errorResponse($result['message'], 500);
            }

            $responseData = $result['data'];
            $responseData['trip_info'] = [
                'id' => $tripInstance->id,
                'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
                'route' => $tripInstance->route->name ?? null,
                'coach' => $tripInstance->coach->name ?? null,
                'status' => $tripInstance->status_name,
            ];

            return $this->successResponse($responseData, 'Seat inventory retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve seat inventory: '.$e->getMessage(), 500);
        }
    }

    public function createSeatInventory(int $id): JsonResponse
    {
        try {
            $tripInstance = TripInstance::findAcrossPartitions($id, now());

            if (! $tripInstance) {
                return $this->errorResponse('Trip instance not found', 404);
            }

            $result = $this->seatInventoryService->createSeatInventoryForTrip($id, $tripInstance->seat_plan_id);

            if (! $result['success']) {
                return $this->errorResponse($result['message'], 500);
            }

            return $this->successResponse($result['data'], $result['message'], 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create seat inventory: '.$e->getMessage(), 500);
        }
    }

    public function blockSeat(int $id, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_id' => 'required|integer',
                'minutes' => 'sometimes|integer|min:1|max:60',
                'user_id' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $result = $this->seatInventoryService->blockSeat(
                $id,
                $request->input('seat_id'),
                $request->input('minutes', 15),
                $request->input('user_id')
            );

            if (! $result['success']) {
                return $this->errorResponse($result['message'], 422);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to block seat: '.$e->getMessage(), 500);
        }
    }

    public function bookSeat(int $id, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_id' => 'required|integer',
                'booking_id' => 'required|integer',
                'user_id' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $result = $this->seatInventoryService->bookSeat(
                $id,
                $request->input('seat_id'),
                $request->input('booking_id'),
                $request->input('user_id')
            );

            if (! $result['success']) {
                return $this->errorResponse($result['message'], 422);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to book seat: '.$e->getMessage(), 500);
        }
    }

    public function releaseSeat(int $id, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $result = $this->seatInventoryService->releaseSeat($id, $request->input('seat_id'));

            if (! $result['success']) {
                return $this->errorResponse($result['message'], 422);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to release seat: '.$e->getMessage(), 500);
        }
    }

    public function cleanupExpiredBlocks(int $id): JsonResponse
    {
        try {
            $result = $this->seatInventoryService->cleanupExpiredBlocks($id);

            if (! $result['success']) {
                return $this->errorResponse($result['message'], 500);
            }

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to cleanup expired blocks: '.$e->getMessage(), 500);
        }
    }
}
