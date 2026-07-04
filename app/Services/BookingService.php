<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\SeatInventory;
use App\Models\TripInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Get a paginated listing of bookings with optional search.
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = min((int) ($attributes['per_page'] ?? 15), 1000);
        $page = max((int) ($attributes['page'] ?? 1), 1);
        $search = $attributes['search'] ?? null;

        return Booking::with(['customer', 'boarding.counter.district', 'dropping.counter.district', 'route', 'bookingDetails.seat'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                })->orWhere('pnr_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a booking by ID with all relationships loaded.
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Booking
    {
        $booking = Booking::with([
            'customer',
            'boarding.counter.district',
            'dropping.counter.district',
            'route',
            'bookingDetails.seat',
        ])->find($id);

        if (! $booking) {
            throw new ModelNotFoundException("Booking with ID {$id} not found.");
        }

        $trip = TripInstance::findAcrossPartitions($booking->trip_id);
        if ($trip) {
            $trip->load(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'boardingDroppings']);
            $booking->setRelation('trip', $trip);
        }

        return $booking;
    }

    /**
     * Create a new booking inside a database transaction.
     */
    public function store(array $attributes): Booking
    {
        $authUserId = auth()->id();

        return DB::transaction(function () use ($attributes, $authUserId) {
            $trip = TripInstance::findAcrossPartitions($attributes['trip_id']);
            if (! $trip) {
                abort(404, 'Trip not found.');
            }
            $trip->load(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'boardingDroppings']);

            $customer = $this->findOrCreateCustomer($attributes, $authUserId);

            $type = (int) $attributes['type'];
            $expireDate = null;
            $expireTime = null;
            if ($type === 2 || $type === 3) {
                if (! empty($attributes['expire_date'])) {
                    $expireDate = date('Y-m-d', strtotime($attributes['expire_date']));
                }
                if (! empty($attributes['expire_time'])) {
                    $expireTime = date('H:i:s', strtotime($attributes['expire_time']));
                }
            }

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'pnr_number' => $this->generateUniquePNR(),
                'trip_id' => $attributes['trip_id'],
                'type' => $type,
                'date' => $attributes['date'],
                'time' => $attributes['time'],
                'note' => $attributes['note'] ?? null,
                'route_id' => $attributes['route_id'],
                'boarding_id' => $attributes['boarding_id'] ?? null,
                'dropping_id' => $attributes['dropping_id'] ?? null,
                'total_price' => 0,
                'total_discount' => 0,
                'total_amount' => 0,
                'expire_date' => $expireDate,
                'expire_time' => $expireTime,
                'created_by' => $authUserId,
            ]);

            $totalPrice = 0;
            $totalDiscount = 0;
            $totalAmount = 0;
            $totalTickets = 0;
            $blockedUntil = $this->calculateBlockedUntil($trip, $type);

            foreach ($attributes['booking_details'] as $detail) {
                $seatInventory = SeatInventory::forTrip($attributes['trip_id'])
                    ->where('id', $detail['seat_inventory_id'])
                    ->first();

                if (! $seatInventory) {
                    abort(404, 'Seat inventory not found.');
                }

                $bookableStatuses = [SeatInventory::STATUS_AVAILABLE, SeatInventory::STATUS_BLOCKED];
                if (! in_array($seatInventory->booking_status, $bookableStatuses)) {
                    abort(400, 'Seat is not available for booking.');
                }

                $discount = $detail['discount'] ?? 0;
                $amount = $detail['price'] - $discount;

                $seatInventory->update([
                    'booking_status' => $type,
                    'booking_id' => $booking->id,
                    'blocked_until' => $blockedUntil,
                    'last_locked_user_id' => $authUserId,
                    'updated_by' => $authUserId,
                ]);

                $booking->bookingDetails()->create([
                    'seat_inventory_id' => $detail['seat_inventory_id'],
                    'seat_id' => $detail['seat_id'],
                    'price' => $detail['price'],
                    'discount' => $discount,
                    'amount' => $amount,
                ]);

                $totalPrice += $detail['price'];
                $totalDiscount += $discount;
                $totalAmount += $amount;
                $totalTickets++;
            }

            $booking->update([
                'total_price' => $totalPrice,
                'total_discount' => $totalDiscount,
                'total_amount' => $totalAmount,
            ]);

            if ($customer->total_trips !== null && $customer->total_tickets !== null) {
                $customer->update([
                    'total_trips' => $customer->total_trips + 1,
                    'total_tickets' => $customer->total_tickets + $totalTickets,
                ]);
            }

            $booking->load(['customer', 'boarding.counter.district', 'dropping.counter.district', 'bookingDetails.seat']);
            $booking->setRelation('trip', $trip);

            return $booking;
        });
    }

    /**
     * Update the specified booking inside a database transaction.
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Booking
    {
        $authUserId = auth()->id();

        return DB::transaction(function () use ($id, $attributes, $authUserId) {
            $booking = Booking::with('bookingDetails')->find($id);
            if (! $booking) {
                throw new ModelNotFoundException("Booking with ID {$id} not found.");
            }

            $trip = TripInstance::findAcrossPartitions($attributes['trip_id']);
            if (! $trip) {
                abort(404, 'Trip not found.');
            }
            $trip->load(['coach', 'bus', 'schedule', 'seatPlan', 'route', 'driver', 'supervisor', 'boardingDroppings']);

            $customer = $this->findOrCreateCustomer($attributes, $authUserId, $booking->customer_id);

            foreach ($booking->bookingDetails as $detail) {
                $seatInventory = SeatInventory::forTrip($booking->trip_id)
                    ->where('id', $detail->seat_inventory_id)
                    ->first();

                if ($seatInventory && $seatInventory->booking_id == $booking->id) {
                    $seatInventory->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'booking_id' => null,
                        'blocked_until' => null,
                        'last_locked_user_id' => null,
                        'updated_by' => $authUserId,
                    ]);
                }
            }

            $booking->bookingDetails()->delete();

            $totalPrice = 0;
            $totalDiscount = 0;
            $totalAmount = 0;
            $type = (int) $attributes['type'];
            $blockedUntil = $this->calculateBlockedUntil($trip, $type);

            foreach ($attributes['booking_details'] as $detail) {
                if ((int) ($detail['cancel_status'] ?? 0) === 1) {
                    continue;
                }

                $seatInventory = SeatInventory::forTrip($attributes['trip_id'])
                    ->where('id', $detail['seat_inventory_id'])
                    ->first();

                if (! $seatInventory) {
                    abort(404, 'Seat inventory not found.');
                }

                $bookableStatuses = [SeatInventory::STATUS_AVAILABLE, SeatInventory::STATUS_BLOCKED];
                if (! in_array($seatInventory->booking_status, $bookableStatuses)) {
                    abort(400, 'Seat is not available for booking.');
                }

                $discount = $detail['discount'] ?? 0;
                $amount = $detail['price'] - $discount;

                $seatInventory->update([
                    'booking_status' => $type,
                    'booking_id' => $booking->id,
                    'blocked_until' => $blockedUntil,
                    'last_locked_user_id' => $authUserId,
                    'updated_by' => $authUserId,
                ]);

                $booking->bookingDetails()->create([
                    'seat_inventory_id' => $detail['seat_inventory_id'],
                    'seat_id' => $detail['seat_id'],
                    'price' => $detail['price'],
                    'discount' => $discount,
                    'amount' => $amount,
                ]);

                $totalPrice += $detail['price'];
                $totalDiscount += $discount;
                $totalAmount += $amount;
            }

            $booking->update([
                'customer_id' => $customer->id,
                'trip_id' => $attributes['trip_id'],
                'type' => $type,
                'date' => $attributes['date'],
                'time' => $attributes['time'],
                'route_id' => $attributes['route_id'],
                'boarding_id' => $attributes['boarding_id'] ?? null,
                'dropping_id' => $attributes['dropping_id'] ?? null,
                'note' => $attributes['note'] ?? null,
                'total_price' => $totalPrice,
                'total_discount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'updated_by' => $authUserId,
            ]);

            $booking->load(['customer', 'boarding.counter.district', 'dropping.counter.district', 'bookingDetails.seat']);
            $booking->setRelation('trip', $trip);

            return $booking;
        });
    }

    /**
     * Find an existing customer by mobile or create a new one.
     */
    private function findOrCreateCustomer(array $attributes, int $authUserId, ?int $existingCustomerId = null): Customer
    {
        $customer = Customer::where('mobile', $attributes['mobile'])->first();

        if ($customer) {
            $customer->update([
                'name' => $attributes['name'],
                'gender' => $attributes['gender'] ?? $customer->gender,
                'age' => $attributes['age'] ?? $customer->age,
                'address' => $attributes['address'] ?? $customer->address,
                'passport_no' => $attributes['passport_no'] ?? $customer->passport_no,
                'nid' => $attributes['nid'] ?? $customer->nid,
                'nationality' => $attributes['nationality'] ?? $customer->nationality,
                'email' => $attributes['email'] ?? $customer->email,
                'updated_by' => $authUserId,
            ]);

            return $customer->fresh();
        }

        return Customer::create([
            'mobile' => $attributes['mobile'],
            'name' => $attributes['name'],
            'gender' => $attributes['gender'] ?? null,
            'age' => $attributes['age'] ?? null,
            'address' => $attributes['address'] ?? null,
            'passport_no' => $attributes['passport_no'] ?? null,
            'nid' => $attributes['nid'] ?? null,
            'nationality' => $attributes['nationality'] ?? 'Bangladeshi',
            'email' => $attributes['email'] ?? null,
            'status' => 1,
            'created_by' => $authUserId,
        ]);
    }

    /**
     * Calculate how long a seat should remain blocked based on booking type.
     */
    private function calculateBlockedUntil(TripInstance $trip, int $type): ?Carbon
    {
        if ($type === 2 || $type === 4) {
            $tripDateTime = Carbon::parse($trip->trip_date);

            if ($trip->schedule && isset($trip->schedule->name)) {
                $departureTime = Carbon::parse($trip->schedule->name);
                $tripDateTime->setTime($departureTime->hour, $departureTime->minute);
            } else {
                $tripDateTime->setTime(8, 0);
            }

            return $tripDateTime->subHour();
        }

        if ($type === 3) {
            return now()->addMinutes(5);
        }

        return null;
    }

    /**
     * Generate a unique PNR number that does not exist in the bookings table.
     */
    private function generateUniquePNR(): string
    {
        do {
            $pnr = $this->generatePNRNumber();
            $exists = Booking::where('pnr_number', $pnr)->exists();
        } while ($exists);

        return $pnr;
    }

    /**
     * Generate a random alphanumeric PNR number.
     */
    private function generatePNRNumber(int $length = 10): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $pnr = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $pnr .= $characters[random_int(0, $maxIndex)];
        }

        return $pnr;
    }
}
