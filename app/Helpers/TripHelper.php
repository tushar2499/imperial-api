<?php

namespace App\Helpers;

class TripHelper
{
    /**
     * Get total seats from seat plan
     */
    public static function getTotalSeats($seatPlanId)
    {
        try {
            if (!$seatPlanId) return 0;

            return \DB::table('seats')
                ->where('seat_plan_id', $seatPlanId)
                ->where('status', '1')
                ->where('is_disable', 0)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get seat inventory summary
     */
    public static function getSeatInventorySummary($trip)
    {
        try {
            $seatInventory = $trip->getSeatInventoryWithDetails();
            $totalSeats = self::getTotalSeats($trip->seat_plan_id);

            if ($seatInventory->isEmpty()) {
                return [
                    'total_seats' => $totalSeats,
                    'available_seats' => $totalSeats,
                    'booked_seats' => 0,
                    'blocked_seats' => 0,
                    'occupancy_percentage' => 0,
                    'has_inventory' => false,
                ];
            }

            $bookedSeats = $seatInventory->where('booking_status', 2)->count();
            $blockedSeats = $seatInventory->where('booking_status', 3)->count();
            $availableSeats = $totalSeats - $bookedSeats - $blockedSeats;
            $occupancyPercentage = $totalSeats > 0 ? round(($bookedSeats / $totalSeats) * 100, 2) : 0;

            return [
                'total_seats' => $totalSeats,
                'available_seats' => max(0, $availableSeats),
                'booked_seats' => $bookedSeats,
                'blocked_seats' => $blockedSeats,
                'occupancy_percentage' => $occupancyPercentage,
                'has_inventory' => true,
            ];
        } catch (\Exception $e) {
            $totalSeats = self::getTotalSeats($trip->seat_plan_id);
            return [
                'total_seats' => $totalSeats,
                'available_seats' => $totalSeats,
                'booked_seats' => 0,
                'blocked_seats' => 0,
                'occupancy_percentage' => 0,
                'has_inventory' => false,
                'error' => 'Failed to load seat inventory',
            ];
        }
    }
}
