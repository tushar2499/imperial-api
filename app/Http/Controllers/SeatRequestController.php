<?php

namespace App\Http\Controllers;

use App\Models\SeatInventory;
use App\Models\TripInstance;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeatRequestController extends Controller
{
    use ApiResponse;

    public function seatRequest(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_inventory_id' => 'required|integer',
                'trip_id' => 'required|integer',
                'issue_id' => 'sometimes|string|max:100',
                'notes' => 'sometimes|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $seatInventoryId = $request->seat_inventory_id;
            $userId = auth()->user()->id;
            $notes = $request->get('notes', '');
            $issueId = $request->get('issue_id') ?: $this->generateIssueId();
            $tripId = $request->trip_id;

            DB::beginTransaction();

            $seatInventory = SeatInventory::forTrip($tripId)
                ->where('id', $seatInventoryId)
                ->first();

            if (! $seatInventory) {
                return $this->errorResponse('Seat inventory not found', 404);
            }

            if ($seatInventory->booking_status != 1) {
                $statusText = match ($seatInventory->booking_status) {
                    2 => 'already booked',
                    3 => 'currently blocked',
                    4 => 'sold',
                    0 => 'cancelled/unavailable',
                    default => 'unavailable'
                };

                return $this->errorResponse("Seat is {$statusText}", 422);
            }

            if ($seatInventory->blocked_until &&
                $seatInventory->blocked_until > now() &&
                $seatInventory->last_locked_user_id != $userId) {

                $blockedUntil = Carbon::parse($seatInventory->blocked_until);
                $remainingMinutes = $blockedUntil->diffInMinutes(now());

                return $this->errorResponse(
                    "Seat is currently blocked by another user for {$remainingMinutes} more minutes",
                    423
                );
            }

            $blockedUntil = now()->addMinutes(5);
            $seatInventory->update([
                'blocked_until' => $blockedUntil,
                'last_locked_user_id' => $userId,
                'updated_at' => now(),
            ]);

            $seatRequest = DB::table('seat_requests')->insertGetId([
                'issue_id' => $issueId,
                'seat_inventory_id' => $seatInventoryId,
                'trip_id' => $seatInventory->trip_id,
                'seat_id' => $seatInventory->seat_id,
                'user_id' => $userId,
                'status' => 'pending',
                'blocked_until' => $blockedUntil,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $seatInfo = $this->getSeatRequestInfo($seatRequest);
            $issueSeats = $this->getIssueSeats($issueId, $userId);

            DB::commit();

            return $this->successResponse([
                'seat_request_id' => $seatRequest,
                'issue_id' => $issueId,
                'seat_inventory_id' => $seatInventoryId,
                'status' => 'pending',
                'blocked_until' => $blockedUntil->toDateTimeString(),
                'blocked_for_minutes' => 5,
                'remaining_time' => [
                    'minutes' => 5,
                    'seconds' => 300,
                    'expires_at' => $blockedUntil->toDateTimeString(),
                ],
                'seat_info' => $seatInfo,
                'user_id' => $userId,
                'created_at' => now()->toDateTimeString(),
                'issue_summary' => [
                    'issue_id' => $issueId,
                    'total_seats_in_issue' => count($issueSeats),
                    'seats' => $issueSeats,
                ],
            ], 'Seat blocked successfully for 5 minutes', 201);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to request seat: '.$e->getMessage(), 500);
        }
    }

    public function seatBookBlockRequest(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_inventory_id' => 'required|integer',
                'trip_id' => 'required|integer',
                'status' => 'required|integer|in:2,3',
                'issue_id' => 'sometimes|string|max:100',
                'notes' => 'sometimes|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $seatInventoryId = $request->seat_inventory_id;
            $userId = auth()->user()->id;
            $notes = $request->get('notes', '');
            $issueId = $request->get('issue_id') ?: $this->generateIssueId();
            $tripId = $request->trip_id;
            $requestedStatus = $request->status;

            DB::beginTransaction();

            $seatInventory = SeatInventory::forTrip($tripId)
                ->where('id', $seatInventoryId)
                ->first();

            if (! $seatInventory) {
                return $this->errorResponse('Seat inventory not found', 404);
            }

            if ($seatInventory->booking_status != SeatInventory::STATUS_AVAILABLE) {
                $statusText = match ($seatInventory->booking_status) {
                    SeatInventory::STATUS_BOOKED => 'already booked',
                    SeatInventory::STATUS_BLOCKED => 'currently blocked',
                    SeatInventory::STATUS_SOLD => 'sold',
                    SeatInventory::STATUS_CANCELLED => 'cancelled/unavailable',
                    default => 'unavailable'
                };

                return $this->errorResponse("Seat is {$statusText}", 422);
            }

            if ($seatInventory->blocked_until &&
                $seatInventory->blocked_until > now() &&
                $seatInventory->last_locked_user_id != $userId) {

                $blockedUntil = Carbon::parse($seatInventory->blocked_until);
                $remainingMinutes = $blockedUntil->diffInMinutes(now());

                return $this->errorResponse(
                    "Seat is currently blocked by another user for {$remainingMinutes} more minutes",
                    423
                );
            }

            $tripInstance = TripInstance::findAcrossPartitions($tripId);
            if (! $tripInstance) {
                return $this->errorResponse('Trip not found', 404);
            }

            $tripDateTime = Carbon::parse($tripInstance->trip_date);
            $tripInstance->load('schedule');

            if ($tripInstance->schedule && isset($tripInstance->schedule->name)) {
                $departureTime = Carbon::parse($tripInstance->schedule->name);
                $tripDateTime->setTime($departureTime->hour, $departureTime->minute);
            } else {
                $tripDateTime->setTime(8, 0);
            }

            $blockedUntilCarbon = null;
            $seatStatus = null;
            $actionMessage = '';

            if ($requestedStatus == 2) {
                $blockedUntilCarbon = $tripDateTime->subHour();
                $seatStatus = SeatInventory::STATUS_BOOKED;
                $actionMessage = 'Seat booked successfully until 1 hour before departure';
            } elseif ($requestedStatus == 3) {
                $blockedUntilCarbon = now()->addMinutes(5);
                $seatStatus = SeatInventory::STATUS_BLOCKED;
                $actionMessage = 'Seat blocked successfully';
            }

            $seatInventory->update([
                'booking_status' => $seatStatus,
                'blocked_until' => $blockedUntilCarbon,
                'last_locked_user_id' => $userId,
                'updated_at' => now(),
            ]);

            $seatRequest = DB::table('seat_requests')->insertGetId([
                'issue_id' => $issueId,
                'seat_inventory_id' => $seatInventoryId,
                'trip_id' => $seatInventory->trip_id,
                'seat_id' => $seatInventory->seat_id,
                'user_id' => $userId,
                'status' => $requestedStatus == 2 ? 'booked' : 'blocked',
                'blocked_until' => $blockedUntilCarbon,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $seatInfo = $this->getSeatRequestInfo($seatRequest);
            $issueSeats = $this->getIssueSeats($issueId, $userId);

            DB::commit();

            $remainingMinutes = $blockedUntilCarbon ? now()->diffInMinutes($blockedUntilCarbon) : 0;
            $remainingSeconds = $blockedUntilCarbon ? now()->diffInSeconds($blockedUntilCarbon) : 0;

            return $this->successResponse([
                'seat_request_id' => $seatRequest,
                'issue_id' => $issueId,
                'seat_inventory_id' => $seatInventoryId,
                'requested_status' => $requestedStatus,
                'seat_status' => $requestedStatus == 2 ? 'booked' : 'blocked',
                'blocked_until' => $blockedUntilCarbon->toDateTimeString(),
                'blocked_for_minutes' => $remainingMinutes,
                'remaining_time' => [
                    'minutes' => max(0, $remainingMinutes),
                    'seconds' => max(0, $remainingSeconds),
                    'expires_at' => $blockedUntilCarbon ? $blockedUntilCarbon->toDateTimeString() : null,
                ],
                'seat_info' => $seatInfo,
                'user_id' => $userId,
                'created_at' => now()->toDateTimeString(),
                'issue_summary' => [
                    'issue_id' => $issueId,
                    'total_seats_in_issue' => count($issueSeats),
                    'seats' => $issueSeats,
                ],
            ], $actionMessage, 201);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to process seat request: '.$e->getMessage(), 500);
        }
    }

    public function seatBookBlockCancel(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_inventory_id' => 'required|integer',
                'trip_id' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $userId = auth()->user()->id;
            $seatInventoryId = $request->seat_inventory_id;
            $tripId = $request->trip_id;

            DB::beginTransaction();

            $seatRequest = DB::table('seat_requests')
                ->where('seat_inventory_id', $seatInventoryId)
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'booked', 'blocked'])
                ->orderBy('id', 'desc')
                ->first();

            if (! $seatRequest) {
                return $this->errorResponse('Seat request not found, already cancelled, or you do not have permission to remove it', 404);
            }

            $seatInventory = SeatInventory::forTrip($tripId)
                ->where('id', $seatInventoryId)
                ->first();

            if (! $seatInventory) {
                return $this->errorResponse('Seat inventory not found', 404);
            }

            if ($seatInventory->last_locked_user_id != $userId) {
                return $this->errorResponse('You do not have permission to remove this seat request', 403);
            }

            DB::table('seat_requests')->where('id', $seatRequest->id)->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

            $seatInventory->update([
                'booking_status' => 1,
                'blocked_until' => null,
                'updated_at' => now(),
            ]);

            $seat = DB::table('seats')->where('id', $seatRequest->seat_id)->first();

            DB::commit();

            return $this->successResponse([
                'seat_inventory_id' => $seatInventoryId,
                'trip_id' => $seatRequest->trip_id,
                'seat_info' => [
                    'seat_id' => $seatRequest->seat_id,
                    'seat_number' => $seat->seat_number ?? null,
                    'row_position' => $seat->row_position ?? null,
                    'col_position' => $seat->col_position ?? null,
                    'seat_type' => $seat->seat_type ?? null,
                ],
                'seat_status' => 'available',
                'request_status' => 'cancelled',
                'blocked_until' => null,
                'user_id' => $userId,
                'cancelled_at' => now()->toDateTimeString(),
            ], 'Seat request cancelled successfully', 200);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to cancel seat request: '.$e->getMessage(), 500);
        }
    }

    public function removeSeatRequest(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_inventory_id' => 'required|integer',
                'issue_id' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $userId = auth()->user()->id;
            $seatInventoryId = $request->seat_inventory_id;
            $issueId = $request->issue_id;

            DB::beginTransaction();

            $seatRequest = DB::table('seat_requests')
                ->where('seat_inventory_id', $seatInventoryId)
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();

            if (! $seatRequest) {
                return $this->errorResponse('Seat request not found, already cancelled, or you do not have permission to remove it', 404);
            }

            $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                ->where('id', $seatInventoryId)
                ->first();

            if (! $seatInventory) {
                return $this->errorResponse('Seat inventory not found', 404);
            }

            if ($seatInventory->last_locked_user_id != $userId) {
                return $this->errorResponse('You do not have permission to remove this seat request', 403);
            }

            DB::table('seat_requests')->where('id', $seatRequest->id)->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

            $seatInventory->update([
                'blocked_until' => null,
                'updated_at' => now(),
            ]);

            $remainingSeats = DB::table('seat_requests')
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->get();

            $seat = DB::table('seats')->where('id', $seatRequest->seat_id)->first();

            DB::commit();

            return $this->successResponse([
                'cancelled_seat_request_id' => $seatRequest->id,
                'seat_inventory_id' => $seatInventoryId,
                'issue_id' => $issueId,
                'trip_id' => $seatRequest->trip_id,
                'seat_info' => [
                    'seat_id' => $seatRequest->seat_id,
                    'seat_number' => $seat->seat_number ?? null,
                    'row_position' => $seat->row_position ?? null,
                    'col_position' => $seat->col_position ?? null,
                    'seat_type' => $seat->seat_type ?? null,
                ],
                'seat_status' => 'available',
                'request_status' => 'cancelled',
                'blocked_until' => null,
                'user_id' => $userId,
                'cancelled_at' => now()->toISOString(),
                'remaining_seats_in_issue' => [
                    'issue_id' => $issueId,
                    'total_remaining_seats' => count($remainingSeats),
                    'seats' => $remainingSeats->map(fn ($s) => [
                        'seat_request_id' => $s->id,
                        'seat_inventory_id' => $s->seat_inventory_id,
                        'seat_id' => $s->seat_id,
                        'status' => $s->status,
                        'blocked_until' => $s->blocked_until,
                    ])->toArray(),
                ],
            ], 'Seat request cancelled successfully', 200);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to cancel seat request: '.$e->getMessage(), 500);
        }
    }

    public function removeAllSeatsFromIssue(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'issue_id' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $userId = auth()->user()->id;
            $issueId = $request->issue_id;

            DB::beginTransaction();

            $seatRequests = DB::table('seat_requests')
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->get();

            if ($seatRequests->isEmpty()) {
                return $this->errorResponse('No pending seat requests found for this issue', 404);
            }

            $cancelledSeats = [];

            foreach ($seatRequests as $seatRequest) {
                DB::table('seat_requests')->where('id', $seatRequest->id)->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);

                $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                    ->where('id', $seatRequest->seat_inventory_id)
                    ->where('last_locked_user_id', $userId)
                    ->first();

                if ($seatInventory) {
                    $seatInventory->update([
                        'blocked_until' => null,
                        'updated_at' => now(),
                    ]);

                    $cancelledSeats[] = [
                        'seat_request_id' => $seatRequest->id,
                        'seat_inventory_id' => $seatRequest->seat_inventory_id,
                        'seat_id' => $seatRequest->seat_id,
                        'status' => 'cancelled',
                    ];
                }
            }

            DB::commit();

            return $this->successResponse([
                'issue_id' => $issueId,
                'cancelled_seats_count' => count($cancelledSeats),
                'cancelled_seats' => $cancelledSeats,
                'user_id' => $userId,
                'cancelled_at' => now()->toISOString(),
            ], 'All seats cancelled from issue successfully', 200);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to cancel seats from issue: '.$e->getMessage(), 500);
        }
    }

    private function generateIssueId(): string
    {
        return 'IE-'.now()->format('Ymd-His').'-'.strtoupper(substr(uniqid(), -6));
    }

    private function getIssueSeats(string $issueId, int $userId): array
    {
        $seats = DB::table('seat_requests as sr')
            ->leftJoin('seats as s', 'sr.seat_id', '=', 's.id')
            ->leftJoin('trip_instances_'.now()->format('Ym').' as ti', 'sr.trip_id', '=', 'ti.id')
            ->where('sr.issue_id', $issueId)
            ->where('sr.user_id', $userId)
            ->select([
                'sr.id as seat_request_id',
                'sr.seat_inventory_id',
                'sr.seat_id',
                'sr.status',
                's.seat_number',
                's.row_position',
                's.col_position',
                's.seat_type',
                'ti.route_id',
                'ti.seat_plan_id',
                'ti.coach_type',
            ])
            ->get();

        return $seats->map(function ($seat) {
            $fareAmount = null;

            if ($seat->seat_type && $seat->route_id) {
                $fare = DB::table('fares')
                    ->where('route_id', $seat->route_id)
                    ->where('seat_plan_id', $seat->seat_plan_id)
                    ->where('coach_type', $seat->coach_type)
                    ->where('seat_type', $seat->seat_type)
                    ->where('status', 1)
                    ->first();

                $fareAmount = $fare->amount ?? null;
            }

            return [
                'seat_request_id' => $seat->seat_request_id,
                'seat_inventory_id' => $seat->seat_inventory_id,
                'seat_id' => $seat->seat_id,
                'status' => $seat->status,
                'seat_number' => $seat->seat_number,
                'row_position' => $seat->row_position,
                'col_position' => $seat->col_position,
                'seat_type' => $seat->seat_type,
                'fare_amount' => $fareAmount,
            ];
        })->toArray();
    }

    private function getSeatRequestInfo(int $seatRequestId): ?array
    {
        try {
            $info = DB::table('seat_requests as sr')
                ->leftJoin('seat_inventories as si', 'sr.seat_inventory_id', '=', 'si.id')
                ->leftJoin('seats as s', 'sr.seat_id', '=', 's.id')
                ->leftJoin('trip_instances_'.now()->format('Ym').' as ti', 'sr.trip_id', '=', 'ti.id')
                ->leftJoin('routes as r', 'ti.route_id', '=', 'r.id')
                ->leftJoin('districts as sd', 'r.start_id', '=', 'sd.id')
                ->leftJoin('districts as ed', 'r.end_id', '=', 'ed.id')
                ->leftJoin('coaches as c', 'ti.coach_id', '=', 'c.id')
                ->where('sr.id', $seatRequestId)
                ->select([
                    's.seat_number', 's.row_position', 's.col_position', 's.seat_type',
                    'ti.trip_date', 'ti.coach_type', 'ti.route_id', 'ti.seat_plan_id',
                    'sr.trip_id', 'c.coach_no', 'r.distance', 'r.duration',
                    'sd.name as start_district', 'ed.name as end_district',
                    'si.booking_status',
                ])
                ->first();

            if (! $info) {
                return null;
            }

            $fareAmount = null;
            if ($info->seat_type && $info->trip_id) {
                $fare = DB::table('fares')
                    ->where('route_id', $info->route_id)
                    ->where('seat_plan_id', $info->seat_plan_id)
                    ->where('coach_type', $info->coach_type)
                    ->where('seat_type', $info->seat_type)
                    ->where('status', 1)
                    ->first();

                $fareAmount = $fare->amount ?? null;
            }

            return [
                'seat' => [
                    'seat_number' => $info->seat_number,
                    'row_position' => $info->row_position,
                    'col_position' => $info->col_position,
                    'seat_type' => $info->seat_type,
                    'fare_amount' => $fareAmount,
                ],
                'trip' => [
                    'trip_date' => $info->trip_date,
                    'coach_no' => $info->coach_no,
                    'coach_type' => $info->coach_type,
                    'coach_type_name' => $info->coach_type == 1 ? 'AC' : 'Non-AC',
                ],
                'route' => [
                    'start_district' => $info->start_district,
                    'end_district' => $info->end_district,
                    'route_display' => ($info->start_district ?? 'Unknown').' → '.($info->end_district ?? 'Unknown'),
                    'distance' => $info->distance,
                    'duration' => $info->duration,
                ],
                'current_status' => [
                    'booking_status' => $info->booking_status,
                    'status_name' => match ($info->booking_status) {
                        1 => 'Available',
                        2 => 'Booked',
                        3 => 'Blocked',
                        0 => 'Cancelled',
                        default => 'Unknown'
                    },
                ],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
