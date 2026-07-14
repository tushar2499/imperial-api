<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\SeatInventory;
use App\Models\SeatRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuestSeatHoldService
{
    public const HOLD_MINUTES = 10;

    /**
     * Hold a seat for an anonymous guest (no user account required).
     *
     * Ownership is tracked by $guestToken (client-generated UUID). The token is stored
     * on seat_requests.guest_token and is required for release or claim.
     */
    public function hold(array $attrs, string $guestToken): array
    {
        $seatInventoryId = (int) $attrs['seat_inventory_id'];
        $tripId          = (int) $attrs['trip_id'];
        $notes           = $attrs['notes'] ?? '';
        $issueId         = $attrs['issue_id'] ?? $this->generateIssueId();

        [$seatRequestId, $blockedUntil] = DB::transaction(
            function () use ($seatInventoryId, $tripId, $notes, $issueId, $guestToken) {
                $seatInventory = SeatInventory::forTrip($tripId)
                    ->where('id', $seatInventoryId)
                    ->lockForUpdate()
                    ->first();

                if (! $seatInventory) {
                    abort(404, 'Seat inventory not found');
                }

                // Auto-release expired blocks before checking availability
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
                        'blocked_until'  => null,
                        'last_locked_user_id' => null,
                    ]);
                    $seatInventory->refresh();
                }

                if ($seatInventory->booking_status !== SeatInventory::STATUS_AVAILABLE) {
                    // If blocked with no user, check if it belongs to this same guest
                    if (
                        $seatInventory->booking_status === SeatInventory::STATUS_BLOCKED
                        && $seatInventory->last_locked_user_id === null
                    ) {
                        $ownerRequest = SeatRequest::query()
                            ->where('seat_inventory_id', $seatInventoryId)
                            ->where('status', 'pending')
                            ->where('guest_token', $guestToken)
                            ->exists();

                        if ($ownerRequest) {
                            abort(409, 'You have already held this seat.');
                        }
                    }

                    $statusText = match ($seatInventory->booking_status) {
                        SeatInventory::STATUS_BOOKED  => 'already booked',
                        SeatInventory::STATUS_BLOCKED => 'currently held by another passenger',
                        SeatInventory::STATUS_SOLD    => 'sold',
                        default                       => 'unavailable',
                    };
                    abort(422, "Seat is {$statusText}");
                }

                $blockedUntil = now()->addMinutes(self::HOLD_MINUTES);

                $seatInventory->update([
                    'booking_status'      => SeatInventory::STATUS_BLOCKED,
                    'blocked_until'       => $blockedUntil,
                    'last_locked_user_id' => null, // guest hold — no user yet
                ]);

                $seatRequest = SeatRequest::create([
                    'issue_id'         => $issueId,
                    'seat_inventory_id' => $seatInventoryId,
                    'trip_id'          => $seatInventory->trip_id,
                    'seat_id'          => $seatInventory->seat_id,
                    'user_id'          => null,
                    'guest_token'      => $guestToken,
                    'status'           => 'pending',
                    'blocked_until'    => $blockedUntil,
                    'notes'            => $notes,
                ]);

                return [$seatRequest->id, $blockedUntil];
            }
        );

        $seat = Seat::find(
            SeatRequest::find($seatRequestId)?->seat_id
        );

        return [
            'seat_request_id'   => $seatRequestId,
            'issue_id'          => $issueId,
            'seat_inventory_id' => $seatInventoryId,
            'status'            => 'pending',
            'blocked_until'     => $blockedUntil->toDateTimeString(),
            'remaining_time'    => [
                'minutes'    => now()->diffInMinutes($blockedUntil),
                'seconds'    => now()->diffInSeconds($blockedUntil),
                'expires_at' => $blockedUntil->toDateTimeString(),
            ],
            'seat_info' => [
                'seat' => [
                    'seat_number'  => $seat?->seat_number,
                    'seat_type'    => $seat?->seat_type,
                    'row_position' => $seat?->row_position,
                    'col_position' => $seat?->col_position,
                    'fare_amount'  => null, // no auth so no fare resolution needed here
                ],
            ],
            'issue_summary' => [
                'issue_id'              => $issueId,
                'total_seats_in_issue'  => SeatRequest::where('issue_id', $issueId)->where('guest_token', $guestToken)->where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * Release a single held seat for the guest identified by $guestToken.
     */
    public function release(array $attrs, string $guestToken): array
    {
        $seatInventoryId = (int) $attrs['seat_inventory_id'];
        $issueId         = $attrs['issue_id'];

        return DB::transaction(function () use ($seatInventoryId, $issueId, $guestToken) {
            $seatRequest = SeatRequest::query()
                ->where('seat_inventory_id', $seatInventoryId)
                ->where('issue_id', $issueId)
                ->where('guest_token', $guestToken)
                ->where('status', 'pending')
                ->first();

            if (! $seatRequest) {
                abort(404, 'Seat request not found or you do not have permission to release it');
            }

            $seatInventory = SeatInventory::forTrip($seatRequest->trip_id)
                ->where('id', $seatInventoryId)
                ->lockForUpdate()
                ->first();

            if ($seatInventory) {
                // Only reset if it's still a guest hold (not yet claimed by a user)
                if ($seatInventory->last_locked_user_id === null) {
                    $seatInventory->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until'  => null,
                    ]);
                }
            }

            $seatRequest->update(['status' => 'cancelled']);

            $seat = Seat::find($seatRequest->seat_id);

            return [
                'seat_inventory_id' => $seatInventoryId,
                'issue_id'          => $issueId,
                'seat_info'         => [
                    'seat_number'  => $seat?->seat_number,
                    'seat_type'    => $seat?->seat_type,
                ],
                'seat_status'    => 'available',
                'request_status' => 'cancelled',
                'cancelled_at'   => now()->toDateTimeString(),
                'remaining_seats_in_issue' => SeatRequest::where('issue_id', $issueId)
                    ->where('guest_token', $guestToken)
                    ->where('status', 'pending')
                    ->count(),
            ];
        });
    }

    /**
     * Release all held seats in an issue for the guest identified by $guestToken.
     */
    public function releaseIssue(string $issueId, string $guestToken): array
    {
        return DB::transaction(function () use ($issueId, $guestToken) {
            $seatRequests = SeatRequest::query()
                ->where('issue_id', $issueId)
                ->where('guest_token', $guestToken)
                ->where('status', 'pending')
                ->get();

            if ($seatRequests->isEmpty()) {
                abort(404, 'No pending seat requests found for this issue and guest token');
            }

            $cancelled = [];

            foreach ($seatRequests as $req) {
                $req->update(['status' => 'cancelled']);

                $inv = SeatInventory::forTrip($req->trip_id)
                    ->where('id', $req->seat_inventory_id)
                    ->where('last_locked_user_id', null) // only release if still a guest hold
                    ->lockForUpdate()
                    ->first();

                if ($inv) {
                    $inv->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until'  => null,
                    ]);
                    $cancelled[] = ['seat_inventory_id' => $req->seat_inventory_id, 'seat_id' => $req->seat_id];
                }
            }

            return [
                'issue_id'            => $issueId,
                'cancelled_count'     => count($cancelled),
                'cancelled_seats'     => $cancelled,
                'cancelled_at'        => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Associate a guest hold with an authenticated user.
     *
     * Called after the user logs in during Step 3. Sets user_id on all pending seat_requests
     * and last_locked_user_id on all affected seat_inventories.
     */
    public function claim(string $issueId, string $guestToken, int $userId): array
    {
        return DB::transaction(function () use ($issueId, $guestToken, $userId) {
            $seatRequests = SeatRequest::query()
                ->where('issue_id', $issueId)
                ->where('guest_token', $guestToken)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($seatRequests->isEmpty()) {
                abort(404, 'No active seat hold found. Your hold may have expired — please reselect seats.');
            }

            $seatRequests->each(fn ($r) => $r->update(['user_id' => $userId]));

            $tripIds = $seatRequests->pluck('trip_id')->unique();
            foreach ($tripIds as $tripId) {
                $inventoryIds = $seatRequests
                    ->where('trip_id', $tripId)
                    ->pluck('seat_inventory_id');

                SeatInventory::forTrip($tripId)
                    ->whereIn('id', $inventoryIds)
                    ->where('last_locked_user_id', null) // confirm still a guest hold
                    ->update(['last_locked_user_id' => $userId]);
            }

            return [
                'issue_id'           => $issueId,
                'user_id'            => $userId,
                'claimed_seats_count' => $seatRequests->count(),
                'claimed_at'         => now()->toDateTimeString(),
            ];
        });
    }

    private function generateIssueId(): string
    {
        return 'GH-'.now()->format('Ymd-His').'-'.strtoupper(substr(uniqid(), -6));
    }
}
