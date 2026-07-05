<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\District;
use App\Models\Fare;
use App\Models\Seat;
use App\Models\SeatInventory;
use App\Models\SeatRequest;
use App\Models\TripInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SeatRequestService
{
    /**
     * Create a pending seat request (5-minute soft block).
     */
    public function store(array $attributes, int $userId): array
    {
        $seatInventoryId = $attributes['seat_inventory_id'];
        $tripId = $attributes['trip_id'];
        $notes = $attributes['notes'] ?? '';
        $issueId = $attributes['issue_id'] ?? $this->generateIssueId();

        [$seatRequestId, $blockedUntil] = DB::transaction(
            function () use ($seatInventoryId, $tripId, $notes, $issueId, $userId) {
                $seatInventory = SeatInventory::forTrip($tripId)
                    ->where('id', $seatInventoryId)
                    ->lockForUpdate()
                    ->first();

                if (! $seatInventory) {
                    abort(404, 'Seat inventory not found');
                }

                if ($seatInventory->booking_status === SeatInventory::STATUS_BLOCKED
                    && $seatInventory->blocked_until
                    && $seatInventory->blocked_until <= now()
                ) {
                    SeatRequest::query()
                        ->where('seat_inventory_id', $seatInventoryId)
                        ->where('status', 'pending')
                        ->update(['status' => 'expired']);

                    $seatInventory->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until' => null,
                        'last_locked_user_id' => null,
                    ]);
                    $seatInventory->refresh();
                }

                if ($seatInventory->booking_status !== SeatInventory::STATUS_AVAILABLE) {
                    $statusText = match ($seatInventory->booking_status) {
                        SeatInventory::STATUS_BOOKED => 'already booked',
                        SeatInventory::STATUS_BLOCKED => 'currently blocked',
                        SeatInventory::STATUS_SOLD => 'sold',
                        SeatInventory::STATUS_CANCELLED => 'cancelled/unavailable',
                        default => 'unavailable',
                    };
                    abort(422, "Seat is {$statusText}");
                }

                if ($seatInventory->blocked_until &&
                    $seatInventory->blocked_until > now() &&
                    $seatInventory->last_locked_user_id !== $userId
                ) {
                    $remainingMinutes = now()->diffInMinutes($seatInventory->blocked_until);
                    abort(423, "Seat is currently blocked by another user for {$remainingMinutes} more minutes");
                }

                $blockedUntil = now()->addMinutes(5);

                $seatInventory->update([
                    'booking_status' => SeatInventory::STATUS_BLOCKED,
                    'blocked_until' => $blockedUntil,
                    'last_locked_user_id' => $userId,
                ]);

                $seatRequest = SeatRequest::create([
                    'issue_id' => $issueId,
                    'seat_inventory_id' => $seatInventoryId,
                    'trip_id' => $seatInventory->trip_id,
                    'seat_id' => $seatInventory->seat_id,
                    'user_id' => $userId,
                    'status' => 'pending',
                    'blocked_until' => $blockedUntil,
                    'notes' => $notes,
                ]);

                return [$seatRequest->id, $blockedUntil];
            }
        );

        $seatInfo = $this->getSeatRequestInfo($seatRequestId, $tripId);
        $issueSeats = $this->getIssueSeats($issueId, $userId);

        return [
            'seat_request_id' => $seatRequestId,
            'issue_id' => $issueId,
            'seat_inventory_id' => $seatInventoryId,
            'status' => 'pending',
            'blocked_until' => $blockedUntil->toDateTimeString(),
            'blocked_for_minutes' => 5,
            'remaining_time' => [
                'minutes' => now()->diffInMinutes($blockedUntil),
                'seconds' => now()->diffInSeconds($blockedUntil),
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
        ];
    }

    /**
     * Directly book (status=2) or block (status=3) a seat.
     */
    public function bookOrBlock(array $attributes, int $userId): array
    {
        $seatInventoryId = $attributes['seat_inventory_id'];
        $tripId = $attributes['trip_id'];
        $notes = $attributes['notes'] ?? '';
        $issueId = $attributes['issue_id'] ?? $this->generateIssueId();
        $requestedStatus = (int) $attributes['status'];

        [$seatRequestId, $blockedUntilCarbon, $seatStatus] = DB::transaction(
            function () use ($seatInventoryId, $tripId, $notes, $issueId, $userId, $requestedStatus) {
                $seatInventory = SeatInventory::forTrip($tripId)
                    ->where('id', $seatInventoryId)
                    ->lockForUpdate()
                    ->first();

                if (! $seatInventory) {
                    abort(404, 'Seat inventory not found');
                }

                if ($seatInventory->booking_status === SeatInventory::STATUS_BLOCKED
                    && $seatInventory->blocked_until
                    && $seatInventory->blocked_until <= now()
                ) {
                    SeatRequest::query()
                        ->where('seat_inventory_id', $seatInventoryId)
                        ->where('status', 'pending')
                        ->update(['status' => 'expired']);

                    $seatInventory->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until' => null,
                        'last_locked_user_id' => null,
                    ]);
                    $seatInventory->refresh();
                }

                if ($seatInventory->booking_status !== SeatInventory::STATUS_AVAILABLE) {
                    $statusText = match ($seatInventory->booking_status) {
                        SeatInventory::STATUS_BOOKED => 'already booked',
                        SeatInventory::STATUS_BLOCKED => 'currently blocked',
                        SeatInventory::STATUS_SOLD => 'sold',
                        SeatInventory::STATUS_CANCELLED => 'cancelled/unavailable',
                        default => 'unavailable',
                    };
                    abort(422, "Seat is {$statusText}");
                }

                if ($seatInventory->blocked_until &&
                    $seatInventory->blocked_until > now() &&
                    $seatInventory->last_locked_user_id !== $userId
                ) {
                    $remainingMinutes = now()->diffInMinutes($seatInventory->blocked_until);
                    abort(423, "Seat is currently blocked by another user for {$remainingMinutes} more minutes");
                }

                $tripInstance = TripInstance::findAcrossPartitions($tripId);
                if (! $tripInstance) {
                    abort(404, 'Trip not found');
                }

                $tripDateTime = Carbon::parse($tripInstance->trip_date);
                $tripInstance->load('schedule');

                if ($tripInstance->schedule && isset($tripInstance->schedule->name)) {
                    $departureTime = Carbon::parse($tripInstance->schedule->name);
                    $tripDateTime->setTime($departureTime->hour, $departureTime->minute);
                } else {
                    $tripDateTime->setTime(8, 0);
                }

                if ($requestedStatus === SeatInventory::STATUS_BOOKED) {
                    $blockedUntilCarbon = $tripDateTime->subHour();
                    $seatStatus = SeatInventory::STATUS_BOOKED;
                } else {
                    $blockedUntilCarbon = now()->addMinutes(5);
                    $seatStatus = SeatInventory::STATUS_BLOCKED;
                }

                $seatInventory->update([
                    'booking_status' => $seatStatus,
                    'blocked_until' => $blockedUntilCarbon,
                    'last_locked_user_id' => $userId,
                ]);

                $seatRequest = SeatRequest::create([
                    'issue_id' => $issueId,
                    'seat_inventory_id' => $seatInventoryId,
                    'trip_id' => $seatInventory->trip_id,
                    'seat_id' => $seatInventory->seat_id,
                    'user_id' => $userId,
                    'status' => $requestedStatus === SeatInventory::STATUS_BOOKED ? 'booked' : 'blocked',
                    'blocked_until' => $blockedUntilCarbon,
                    'notes' => $notes,
                ]);

                return [$seatRequest->id, $blockedUntilCarbon, $seatStatus];
            }
        );

        $seatInfo = $this->getSeatRequestInfo($seatRequestId, $tripId);
        $issueSeats = $this->getIssueSeats($issueId, $userId);

        $remainingMinutes = now()->diffInMinutes($blockedUntilCarbon);
        $remainingSeconds = now()->diffInSeconds($blockedUntilCarbon);

        return [
            'seat_request_id' => $seatRequestId,
            'issue_id' => $issueId,
            'seat_inventory_id' => $seatInventoryId,
            'requested_status' => $requestedStatus,
            'seat_status' => $requestedStatus === SeatInventory::STATUS_BOOKED ? 'booked' : 'blocked',
            'blocked_until' => $blockedUntilCarbon->toDateTimeString(),
            'blocked_for_minutes' => $remainingMinutes,
            'remaining_time' => [
                'minutes' => max(0, $remainingMinutes),
                'seconds' => max(0, $remainingSeconds),
                'expires_at' => $blockedUntilCarbon->toDateTimeString(),
            ],
            'seat_info' => $seatInfo,
            'user_id' => $userId,
            'created_at' => now()->toDateTimeString(),
            'issue_summary' => [
                'issue_id' => $issueId,
                'total_seats_in_issue' => count($issueSeats),
                'seats' => $issueSeats,
            ],
        ];
    }

    /**
     * Cancel a booked/blocked seat request (reset to available).
     */
    public function cancelBookBlock(array $attributes, int $userId): array
    {
        $seatInventoryId = $attributes['seat_inventory_id'];
        $tripId = $attributes['trip_id'];

        return DB::transaction(function () use ($seatInventoryId, $tripId, $userId) {
            $seatRequest = SeatRequest::query()
                ->where('seat_inventory_id', $seatInventoryId)
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'booked', 'blocked'])
                ->orderBy('id', 'desc')
                ->first();

            if (! $seatRequest) {
                abort(404, 'Seat request not found, already cancelled, or you do not have permission to remove it');
            }

            $seatInventory = SeatInventory::forTrip($tripId)
                ->where('id', $seatInventoryId)
                ->lockForUpdate()
                ->first();

            if (! $seatInventory) {
                abort(404, 'Seat inventory not found');
            }

            if ($seatInventory->last_locked_user_id !== $userId) {
                abort(403, 'You do not have permission to remove this seat request');
            }

            $seatRequest->update(['status' => 'cancelled']);

            $seatInventory->update([
                'booking_status' => SeatInventory::STATUS_AVAILABLE,
                'blocked_until' => null,
            ]);

            $seat = Seat::query()->find($seatRequest->seat_id);

            return [
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
            ];
        });
    }

    /**
     * Cancel a single pending seat request within an issue.
     */
    public function cancel(array $attributes, int $userId): array
    {
        $seatInventoryId = $attributes['seat_inventory_id'];
        $issueId = $attributes['issue_id'];

        return DB::transaction(function () use ($seatInventoryId, $issueId, $userId) {
            $seatRequest = SeatRequest::query()
                ->where('seat_inventory_id', $seatInventoryId)
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();

            if (! $seatRequest) {
                abort(404, 'Seat request not found, already cancelled, or you do not have permission to remove it');
            }

            $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                ->where('id', $seatInventoryId)
                ->lockForUpdate()
                ->first();

            if (! $seatInventory) {
                abort(404, 'Seat inventory not found');
            }

            if ($seatInventory->last_locked_user_id !== $userId) {
                abort(403, 'You do not have permission to remove this seat request');
            }

            $seatRequest->update(['status' => 'cancelled']);

            $seatInventory->update([
                'booking_status' => SeatInventory::STATUS_AVAILABLE,
                'blocked_until' => null,
            ]);

            $remainingSeats = SeatRequest::query()
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->get();

            $seat = Seat::query()->find($seatRequest->seat_id);

            return [
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
            ];
        });
    }

    /**
     * Cancel all pending seat requests in an issue.
     */
    public function cancelIssue(array $attributes, int $userId): array
    {
        $issueId = $attributes['issue_id'];

        return DB::transaction(function () use ($issueId, $userId) {
            $seatRequests = SeatRequest::query()
                ->where('issue_id', $issueId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->get();

            if ($seatRequests->isEmpty()) {
                abort(404, 'No pending seat requests found for this issue');
            }

            $cancelledSeats = [];

            foreach ($seatRequests as $seatRequest) {
                $seatRequest->update(['status' => 'cancelled']);

                $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                    ->where('id', $seatRequest->seat_inventory_id)
                    ->where('last_locked_user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($seatInventory) {
                    $seatInventory->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until' => null,
                    ]);

                    $cancelledSeats[] = [
                        'seat_request_id' => $seatRequest->id,
                        'seat_inventory_id' => $seatRequest->seat_inventory_id,
                        'seat_id' => $seatRequest->seat_id,
                        'status' => 'cancelled',
                    ];
                }
            }

            return [
                'issue_id' => $issueId,
                'cancelled_seats_count' => count($cancelledSeats),
                'cancelled_seats' => $cancelledSeats,
                'user_id' => $userId,
                'cancelled_at' => now()->toISOString(),
            ];
        });
    }

    /**
     * Look up the active fare amount for a seat on a given trip.
     *
     * Fares are matched by route + coach_type + seat_type + trip date only.
     * seat_plan_id is intentionally excluded: the trip's seat_plan_id reflects
     * the coach's physical layout, while fares are categorised under a separate
     * seat plan concept — joining on it would cause mismatches.
     *
     * The validForDate scope covers both cases from fare creation:
     *   Case 1 (dated fare): from_date <= trip_date <= to_date
     *   Case 2 (open fare):  from_date or to_date is null — always valid
     *
     * @param  object|null  $seat
     * @param  TripInstance|null  $tripInstance
     * @return string|null
     */
    private function resolveFareAmount(?object $seat, ?TripInstance $tripInstance): ?string
    {
        if (! $seat || ! $tripInstance || ! $seat->seat_type) {
            return null;
        }

        $fare = Fare::active()
            ->where('route_id', $tripInstance->route_id)
            ->where('coach_type', $tripInstance->coach_type)
            ->where('seat_type', $seat->seat_type)
            ->validForDate($tripInstance->trip_date)
            ->first();

        return $fare?->amount !== null ? (string) $fare->amount : null;
    }

    private function generateIssueId(): string
    {
        return 'IE-'.now()->format('Ymd-His').'-'.strtoupper(substr(uniqid(), -6));
    }

    private function getIssueSeats(string $issueId, int $userId): array
    {
        $seatRequests = SeatRequest::query()
            ->where('issue_id', $issueId)
            ->where('user_id', $userId)
            ->get();

        return $seatRequests->map(function ($sr) {
            $seat = Seat::query()->find($sr->seat_id);
            $tripInstance = TripInstance::findAcrossPartitions($sr->trip_id);

            return [
                'seat_request_id' => $sr->id,
                'seat_inventory_id' => $sr->seat_inventory_id,
                'seat_id' => $sr->seat_id,
                'status' => $sr->status,
                'seat_number' => $seat->seat_number ?? null,
                'row_position' => $seat->row_position ?? null,
                'col_position' => $seat->col_position ?? null,
                'seat_type' => $seat->seat_type ?? null,
                'fare_amount' => $this->resolveFareAmount($seat, $tripInstance),
            ];
        })->toArray();
    }

    private function getSeatRequestInfo(int $seatRequestId, int $tripId): ?array
    {
        try {
            $seatRequest = SeatRequest::find($seatRequestId);
            if (! $seatRequest) {
                return null;
            }

            $seat = Seat::query()->find($seatRequest->seat_id);
            $tripInstance = TripInstance::findAcrossPartitions($tripId);
            if (! $tripInstance) {
                return null;
            }

            $route = DB::table('routes')->where('id', $tripInstance->route_id)->first();
            $startDistrict = $route ? District::query()->find($route->start_id) : null;
            $endDistrict = $route ? District::query()->find($route->end_id) : null;
            $coach = Coach::query()->find($tripInstance->coach_id);

            $seatInventory = SeatInventory::forTrip($tripId)
                ->where('id', $seatRequest->seat_inventory_id)
                ->first();

            return [
                'seat' => [
                    'seat_number' => $seat->seat_number ?? null,
                    'row_position' => $seat->row_position ?? null,
                    'col_position' => $seat->col_position ?? null,
                    'seat_type' => $seat->seat_type ?? null,
                    'fare_amount' => $this->resolveFareAmount($seat, $tripInstance),
                ],
                'trip' => [
                    'trip_date' => $tripInstance->trip_date,
                    'coach_no' => $coach->coach_no ?? null,
                    'coach_type' => $tripInstance->coach_type,
                    'coach_type_name' => $tripInstance->coach_type == 1 ? 'AC' : 'Non-AC',
                ],
                'route' => [
                    'start_district' => $startDistrict->name ?? null,
                    'end_district' => $endDistrict->name ?? null,
                    'route_display' => ($startDistrict->name ?? 'Unknown').' → '.($endDistrict->name ?? 'Unknown'),
                    'distance' => $route->distance ?? null,
                    'duration' => $route->duration ?? null,
                ],
                'current_status' => [
                    'booking_status' => $seatInventory?->booking_status,
                    'status_name' => match ($seatInventory?->booking_status) {
                        SeatInventory::STATUS_AVAILABLE => 'Available',
                        SeatInventory::STATUS_BOOKED => 'Booked',
                        SeatInventory::STATUS_BLOCKED => 'Blocked',
                        SeatInventory::STATUS_CANCELLED => 'Cancelled',
                        default => 'Unknown',
                    },
                ],
            ];
        } catch (\Exception) {
            return null;
        }
    }
}
