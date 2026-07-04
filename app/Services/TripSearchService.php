<?php

namespace App\Services;

use App\Helpers\TripHelper;
use App\Models\Counter;
use App\Models\Fare;
use App\Models\SeatInventory;
use App\Models\TripInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TripSearchService
{
    /**
     * Execute the trip search query and return paginated trips with counters.
     *
     * @param  array<string, mixed>  $validated  Validated input from SearchTripsRequest
     * @return array{trips: \Illuminate\Pagination\LengthAwarePaginator, tripIds: array, boardingCounters: \Illuminate\Database\Eloquent\Collection, droppingCounters: \Illuminate\Database\Eloquent\Collection}
     */
    public function search(array $validated): array
    {
        $tripDate = Carbon::parse($validated['trip_date']);
        $routeStartId = $validated['route_start_id'];
        $routeEndId = $validated['route_end_id'];
        $coachType = $validated['coach_type'] ?? null;
        $boardingCounterId = $validated['boarding_counter_id'] ?? null;
        $droppingCounterId = $validated['dropping_counter_id'] ?? null;

        $query = TripInstance::forDate($tripDate)
            ->active()
            ->byDate($tripDate)
            ->with([
                'boardingDroppings.counter',
                'route.startDistrict',
                'route.endDistrict',
                'coach',
                'bus',
                'schedule',
                'seatPlan',
                'driver',
                'supervisor',
            ])
            ->whereHas('route', function ($routeQuery) use ($routeStartId, $routeEndId): void {
                $routeQuery->where('start_id', $routeStartId)
                    ->where('end_id', $routeEndId);
            });

        if (! empty($validated['coach_no'])) {
            $query->whereHas('coach', fn ($q) => $q->where('coach_no', $validated['coach_no']));
        }

        if (! empty($validated['schedule_id'])) {
            $query->where('schedule_id', $validated['schedule_id']);
        }

        if ($coachType) {
            $query->where('coach_type', $coachType);
        }

        if ($boardingCounterId) {
            $query->whereHas('boardingDropping', fn ($q) => $q->where('counter_id', $boardingCounterId)->where('type', 1));
        }

        if ($droppingCounterId) {
            $query->whereHas('boardingDropping', fn ($q) => $q->where('counter_id', $droppingCounterId)->where('type', 2));
        }

        $query->orderBy('trip_date', 'asc')->orderBy('created_at', 'desc');

        $perPage = (int) ($validated['per_page'] ?? 15);
        $trips = $query->paginate($perPage);

        $tripIds = $trips->getCollection()->pluck('id')->toArray();

        $transformedTrips = $trips->getCollection()->map(fn (TripInstance $trip) => $this->transformTrip($trip));
        $trips->setCollection($transformedTrips);

        $boardingCounters = Counter::where('status', 1)
            ->whereHas('tripBoardingDroppings', fn ($q) => $q->whereIn('trip_id', $tripIds)->where('type', 1))
            ->get();

        $droppingCounters = Counter::where('status', 1)
            ->whereHas('tripBoardingDroppings', fn ($q) => $q->whereIn('trip_id', $tripIds)->where('type', 2))
            ->get();

        return compact('trips', 'tripIds', 'boardingCounters', 'droppingCounters');
    }

    /**
     * Transform a TripInstance model into the API response array.
     *
     * @param  TripInstance  $trip
     * @return array<string, mixed>
     */
    private function transformTrip(TripInstance $trip): array
    {
        $boardingCounter = null;
        $startingPoint = $trip->boardingDroppings->where('starting_point_status', 1)->first();
        if ($startingPoint?->counter) {
            $boardingCounter = [
                'id' => $startingPoint->counter->id,
                'name' => $startingPoint->counter->address,
                'location' => $startingPoint->counter->land_mark,
                'contact' => $startingPoint->counter->mobile ?? null,
                'time' => $startingPoint->time,
            ];
        }

        $droppingCounter = null;
        $endingPoint = $trip->boardingDroppings->where('ending_point_status', 1)->first();
        if ($endingPoint?->counter) {
            $droppingCounter = [
                'id' => $endingPoint->counter->id,
                'name' => $endingPoint->counter->address,
                'location' => $endingPoint->counter->land_mark,
                'contact' => $endingPoint->counter->mobile ?? null,
                'time' => $endingPoint->time,
            ];
        }

        $activeFares = $trip->getActiveFares();

        $faresInfo = $activeFares->map(fn ($fare) => [
            'fare_id' => $fare->id,
            'seat_type' => $fare->seat_type,
            'amount' => $fare->amount ?? null,
            'coach_type' => $fare->coach_type_name,
            'route_id' => $fare->route_id,
            'seat_plan_id' => $fare->seat_plan_id,
            'status' => $fare->status_name,
            'from_date' => $fare->from_date?->format('Y-m-d H:i:s'),
            'to_date' => $fare->to_date?->format('Y-m-d H:i:s'),
        ])->toArray();

        $defaultFare = $activeFares->firstWhere('seat_type', Fare::SEAT_TYPE_ECONOMY) ?? $activeFares->first();
        $defaultFareInfo = $defaultFare ? [
            'fare_id' => $defaultFare->id,
            'seat_type' => $defaultFare->seat_type,
            'amount' => $defaultFare->amount ?? null,
            'coach_type' => $defaultFare->coach_type_name,
        ] : null;

        $seatInventorySummary = $this->getSeatInventorySummary($trip);

        return [
            'trip_id' => $trip->id,
            'trip_date' => $trip->formatted_trip_date,
            'trip_date_formatted' => $trip->trip_date->format('l, F j, Y'),
            'status' => $trip->status,
            'status_name' => $trip->status_name,
            'coach_type' => $trip->coach_type,
            'coach_type_name' => $trip->coach_type_name,
            'current_partition' => $trip->current_partition,
            'coach_id' => $trip->coach?->id,
            'coach_no' => $trip->coach?->coach_no,
            'coach_seat_plan_id' => $trip->coach?->seat_plan_id,
            'coach_status' => $trip->coach?->status,
            'bus_id' => $trip->bus?->id,
            'bus_registration_number' => $trip->bus?->registration_number,
            'bus_manufacturer' => $trip->bus?->manufacturer_company,
            'bus_model_year' => $trip->bus?->model_year,
            'schedule_id' => $trip->schedule?->id,
            'schedule_name' => $trip->schedule?->name,
            'route_id' => $trip->route?->id,
            'start_id' => $trip->route?->start_id,
            'end_id' => $trip->route?->end_id,
            'distance' => $trip->route?->distance,
            'duration' => $trip->route?->duration,
            'start_district_name' => $trip->route?->startDistrict?->name ?? 'Unknown',
            'end_district_name' => $trip->route?->endDistrict?->name ?? 'Unknown',
            'route_display' => sprintf(
                '%s → %s',
                $trip->route?->startDistrict?->name ?? 'Unknown',
                $trip->route?->endDistrict?->name ?? 'Unknown'
            ),
            'boarding_counter' => $boardingCounter,
            'dropping_counter' => $droppingCounter,
            'driver_id' => $trip->driver_id,
            'driver_name' => $trip->driver?->name,
            'driver_contact' => $trip->driver?->contact_no,
            'driver_license' => $trip->driver?->license_no,
            'supervisor_id' => $trip->supervisor_id,
            'supervisor_name' => $trip->supervisor?->name,
            'supervisor_contact' => $trip->supervisor?->contact_no,
            'seat_plan_id' => $trip->seatPlan?->id,
            'seat_plan_name' => $trip->seatPlan?->name,
            'seat_plan_floors' => $trip->seatPlan?->floor,
            'seat_plan_rows' => $trip->seatPlan?->rows,
            'seat_plan_cols' => $trip->seatPlan?->cols,
            'total_seats' => TripHelper::getTotalSeats($trip->seat_plan_id),
            'seat_inventory_summary' => $seatInventorySummary,
            'sold_seats_count' => $seatInventorySummary['sold'] ?? 0,
            'available_seats_count' => $seatInventorySummary['available'] ?? 0,
            'booked_seats_count' => $seatInventorySummary['booked'] ?? 0,
            'blocked_seats_count' => $seatInventorySummary['blocked'] ?? 0,
            'cancelled_seats_count' => $seatInventorySummary['cancelled'] ?? 0,
            'availability_percentage' => $this->calculateAvailabilityPercentage($seatInventorySummary),
            'fares' => $faresInfo,
            'available_seat_types' => $activeFares->pluck('seat_type')->unique()->values()->toArray(),
            'is_ac' => $trip->isAC(),
            'is_active' => $trip->isActive(),
            'is_migrated' => $trip->isMigrated(),
            'created_at' => $trip->created_at,
            'updated_at' => $trip->updated_at,
        ];
    }

    /**
     * Get seat inventory counts for a trip, grouped by booking status.
     *
     * Uses the trip's already-loaded date to target the correct partition directly,
     * avoiding the extra findAcrossPartitions() query that SeatInventory::forTrip() would do.
     *
     * @param  TripInstance  $trip
     * @return array{total: int, available: int, booked: int, blocked: int, cancelled: int, sold: int, occupied: int}
     */
    private function getSeatInventorySummary(TripInstance $trip): array
    {
        try {
            $instance = new SeatInventory;
            $instance->usePartition($trip->trip_date);
            $inventories = $instance->newQuery()->where('trip_id', $trip->id)->get();

            $summary = [
                'total' => $inventories->count(),
                'available' => $inventories->where('booking_status', SeatInventory::STATUS_AVAILABLE)->count(),
                'booked' => $inventories->where('booking_status', SeatInventory::STATUS_BOOKED)->count(),
                'blocked' => $inventories->where('booking_status', SeatInventory::STATUS_BLOCKED)->count(),
                'cancelled' => $inventories->where('booking_status', SeatInventory::STATUS_CANCELLED)->count(),
                'sold' => $inventories->where('booking_status', SeatInventory::STATUS_SOLD)->count(),
            ];

            $summary['occupied'] = $summary['booked'] + $summary['sold'];

            return $summary;
        } catch (\Exception $e) {
            Log::error("Failed to get seat inventory summary for trip {$trip->id}: ".$e->getMessage());

            return ['total' => 0, 'available' => 0, 'booked' => 0, 'blocked' => 0, 'cancelled' => 0, 'sold' => 0, 'occupied' => 0];
        }
    }

    /**
     * Calculate the percentage of seats still available for a trip.
     *
     * @param  array{total: int, available: int}  $summary
     * @return float
     */
    private function calculateAvailabilityPercentage(array $summary): float
    {
        $total = $summary['total'] ?? 0;

        if ($total === 0) {
            return 0.0;
        }

        return round(($summary['available'] / $total) * 100, 2);
    }
}
