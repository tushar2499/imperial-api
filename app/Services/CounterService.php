<?php

namespace App\Services;

use App\Models\Counter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CounterService
{
    /**
     * Get a paginated listing of counters with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Counter::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('address', 'like', "%{$search}%")
                    ->orWhere('land_mark', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active counters.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return Counter::where('status', 1)->latest()->get();
    }

    /**
     * Create a new counter inside a database transaction.
     *
     * @param  array  $attributes
     * @return Counter
     */
    public function store(array $attributes): Counter
    {
        return DB::transaction(fn () => Counter::create([
            'type'                   => $attributes['type'],
            'address'                => $attributes['address'],
            'land_mark'              => $attributes['land_mark'] ?? null,
            'location_url'           => $attributes['location_url'] ?? null,
            'phone'                  => $attributes['phone'] ?? null,
            'mobile'                 => $attributes['mobile'] ?? null,
            'email'                  => $attributes['email'] ?? null,
            'primary_contact_no'     => $attributes['primary_contact_no'] ?? null,
            'country'                => $attributes['country'] ?? null,
            'district_id'            => $attributes['district_id'],
            'booking_allowed_status' => $attributes['booking_allowed_status'],
            'booking_allowed_class'  => $attributes['booking_allowed_class'],
            'no_of_boarding_allowed' => $attributes['no_of_boarding_allowed'] ?? null,
            'sms_status'             => $attributes['sms_status'] ?? 1,
            'status'                 => $attributes['status'] ?? 1,
            'created_by'             => auth()->id(),
        ]));
    }

    /**
     * Find a counter by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Counter
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Counter
    {
        $counter = Counter::find($id);

        if (! $counter) {
            throw new ModelNotFoundException("Counter with ID {$id} not found.");
        }

        return $counter;
    }

    /**
     * Update the specified counter inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Counter
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Counter
    {
        return DB::transaction(function () use ($id, $attributes) {
            $counter = $this->findById($id);

            $counter->update([
                'type'                   => $attributes['type'],
                'address'                => $attributes['address'],
                'land_mark'              => $attributes['land_mark'] ?? null,
                'location_url'           => $attributes['location_url'] ?? null,
                'phone'                  => $attributes['phone'] ?? null,
                'mobile'                 => $attributes['mobile'] ?? null,
                'email'                  => $attributes['email'] ?? null,
                'primary_contact_no'     => $attributes['primary_contact_no'] ?? null,
                'country'                => $attributes['country'] ?? null,
                'district_id'            => $attributes['district_id'],
                'booking_allowed_status' => $attributes['booking_allowed_status'],
                'booking_allowed_class'  => $attributes['booking_allowed_class'],
                'no_of_boarding_allowed' => $attributes['no_of_boarding_allowed'] ?? null,
                'sms_status'             => $attributes['sms_status'] ?? $counter->sms_status,
                'status'                 => $attributes['status'] ?? $counter->status,
                'updated_by'             => auth()->id(),
            ]);

            return $counter->fresh();
        });
    }

    /**
     * Soft-delete the specified counter inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->findById($id)->delete();
        });
    }
}
