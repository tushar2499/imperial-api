<?php

namespace App\Services;

use App\Models\SeatInventory;
use App\Models\TripInstance;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeatInventoryService
{
    /**
     * Create seat inventory for a trip instance
     *
     * @param int $tripId
     * @param int|null $seatPlanId
     * @return array
     */
    public function createSeatInventoryForTrip($tripId, $seatPlanId = null): array
    {
        try {
            DB::beginTransaction();

            // Get trip instance to determine seat plan
            $tripInstance = TripInstance::findAcrossPartitions($tripId);

            if (!$tripInstance) {
                throw new \Exception("Trip instance not found: {$tripId}");
            }

            $seatPlanId = $seatPlanId ?: $tripInstance->seat_plan_id;

            // Get all seats for the seat plan
            $seats = Seat::where('seat_plan_id', $seatPlanId)->get();

            if ($seats->isEmpty()) {
                throw new \Exception("No seats found for seat plan: {$seatPlanId}");
            }

            $createdInventories = [];

            foreach ($seats as $seat) {
                // Check if seat inventory already exists
                $existingInventory = SeatInventory::forTrip($tripId)
                    ->where('seat_id', $seat->id)
                    ->first();

                if (!$existingInventory) {
                    $inventory = SeatInventory::create([
                        'trip_id' => $tripId,
                        'seat_id' => $seat->id,
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'blocked_until' => null,
                        'booking_id' => null,
                        'last_locked_user_id' => null,
                        'created_by' => auth()->user()->id ?? null,
                    ]);

                    $createdInventories[] = $inventory;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Seat inventory created successfully',
                'data' => [
                    'trip_id' => $tripId,
                    'total_seats' => $seats->count(),
                    'created_inventories' => count($createdInventories),
                    'inventories' => $createdInventories
                ]
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to create seat inventory: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get seat inventory for a trip
     *
     * @param int $tripId
     * @param array $filters
     * @return array
     */
    public function getTripSeatInventory($tripId, array $filters = []): array
    {
        try {
            $query = SeatInventory::forTrip($tripId)
                ->with(['seat', 'booking', 'lastLockedUser']);

            // Apply filters
            if (isset($filters['booking_status'])) {
                $query->where('booking_status', $filters['booking_status']);
            }

            if (isset($filters['seat_type'])) {
                $query->whereHas('seat', function ($q) use ($filters) {
                    $q->where('seat_type', $filters['seat_type']);
                });
            }

            $inventories = $query->get();

            // Group by status for summary
            $summary = [
                'total' => $inventories->count(),
                'available' => $inventories->where('booking_status', SeatInventory::STATUS_AVAILABLE)->count(),
                'booked' => $inventories->where('booking_status', SeatInventory::STATUS_BOOKED)->count(),
                'blocked' => $inventories->where('booking_status', SeatInventory::STATUS_BLOCKED)->count(),
                'cancelled' => $inventories->where('booking_status', SeatInventory::STATUS_CANCELLED)->count(),
            ];

            return [
                'success' => true,
                'data' => [
                    'trip_id' => $tripId,
                    'summary' => $summary,
                    'inventories' => $inventories
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get seat inventory: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Block a seat temporarily
     *
     * @param int $tripId
     * @param int $seatId
     * @param int $minutes
     * @param int|null $userId
     * @return array
     */
    public function blockSeat($tripId, $seatId, $minutes = 15, $userId = null): array
    {
        try {
            DB::beginTransaction();

            $inventory = SeatInventory::forTrip($tripId)
                ->where('seat_id', $seatId)
                ->first();

            if (!$inventory) {
                throw new \Exception("Seat inventory not found for trip {$tripId}, seat {$seatId}");
            }

            if (!$inventory->isAvailable()) {
                throw new \Exception("Seat is not available for blocking. Current status: {$inventory->booking_status_name}");
            }

            $inventory->update([
                'booking_status' => SeatInventory::STATUS_BLOCKED,
                'blocked_until' => now()->addMinutes($minutes),
                'last_locked_user_id' => $userId ?: auth()->user()->id ?? null,
                'updated_by' => auth()->user()->id ?? null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Seat blocked successfully',
                'data' => $inventory->refresh()
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to block seat: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Book a seat
     *
     * @param int $tripId
     * @param int $seatId
     * @param int $bookingId
     * @param int|null $userId
     * @return array
     */
    public function bookSeat($tripId, $seatId, $bookingId, $userId = null): array
    {
        try {
            DB::beginTransaction();

            $inventory = SeatInventory::forTrip($tripId)
                ->where('seat_id', $seatId)
                ->first();

            if (!$inventory) {
                throw new \Exception("Seat inventory not found for trip {$tripId}, seat {$seatId}");
            }

            if (!$inventory->isAvailable() && !$inventory->isBlocked()) {
                throw new \Exception("Seat is not available for booking. Current status: {$inventory->booking_status_name}");
            }

            // If blocked, check if it's blocked by the same user
            if ($inventory->isBlocked()) {
                $currentUserId = $userId ?: auth()->user()->id ?? null;
                if ($inventory->last_locked_user_id !== $currentUserId) {
                    throw new \Exception("Seat is blocked by another user");
                }
            }

            $inventory->update([
                'booking_status' => SeatInventory::STATUS_BOOKED,
                'blocked_until' => null,
                'booking_id' => $bookingId,
                'last_locked_user_id' => $userId ?: auth()->user()->id ?? null,
                'updated_by' => auth()->user()->id ?? null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Seat booked successfully',
                'data' => $inventory->refresh()
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to book seat: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Release a seat (make it available)
     *
     * @param int $tripId
     * @param int $seatId
     * @return array
     */
    public function releaseSeat($tripId, $seatId): array
    {
        try {
            DB::beginTransaction();

            $inventory = SeatInventory::forTrip($tripId)
                ->where('seat_id', $seatId)
                ->first();

            if (!$inventory) {
                throw new \Exception("Seat inventory not found for trip {$tripId}, seat {$seatId}");
            }

            $inventory->update([
                'booking_status' => SeatInventory::STATUS_AVAILABLE,
                'blocked_until' => null,
                'booking_id' => null,
                'last_locked_user_id' => null,
                'updated_by' => auth()->user()->id ?? null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Seat released successfully',
                'data' => $inventory->refresh()
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to release seat: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Cancel a seat booking
     *
     * @param int $tripId
     * @param int $seatId
     * @return array
     */
    public function cancelSeat($tripId, $seatId): array
    {
        try {
            DB::beginTransaction();

            $inventory = SeatInventory::forTrip($tripId)
                ->where('seat_id', $seatId)
                ->first();

            if (!$inventory) {
                throw new \Exception("Seat inventory not found for trip {$tripId}, seat {$seatId}");
            }

            $inventory->update([
                'booking_status' => SeatInventory::STATUS_CANCELLED,
                'blocked_until' => null,
                'updated_by' => auth()->user()->id ?? null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Seat cancelled successfully',
                'data' => $inventory->refresh()
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to cancel seat: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Clean up expired blocks
     *
     * @param int|null $tripId
     * @return array
     */
    public function cleanupExpiredBlocks($tripId = null): array
    {
        try {
            DB::beginTransaction();

            $query = SeatInventory::expiredBlocks();

            if ($tripId) {
                $query->forTrip($tripId);
            }

            $expiredInventories = $query->get();
            $cleanedCount = 0;

            foreach ($expiredInventories as $inventory) {
                $inventory->update([
                    'booking_status' => SeatInventory::STATUS_AVAILABLE,
                    'blocked_until' => null,
                    'last_locked_user_id' => null,
                    'updated_by' => auth()->user()->id ?? null,
                ]);
                $cleanedCount++;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Cleaned up {$cleanedCount} expired blocks",
                'data' => [
                    'cleaned_count' => $cleanedCount,
                    'trip_id' => $tripId
                ]
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to cleanup expired blocks: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
