<?php

namespace App\Services;

use App\Models\Bus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BusService
{
    /**
     * Get a paginated listing of buses with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Bus::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('manufacturer_company', 'like', "%{$search}%")
                    ->orWhere('chasis_no', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhere('lc_code_number', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new bus inside a database transaction.
     *
     * @param  array  $attributes
     * @return Bus
     */
    public function store(array $attributes): Bus
    {
        return DB::transaction(fn () => Bus::create([
            'registration_number' => $attributes['registration_number'],
            'manufacturer_company' => $attributes['manufacturer_company'],
            'model_year' => $attributes['model_year'],
            'chasis_no' => $attributes['chasis_no'],
            'engine_number' => $attributes['engine_number'],
            'country_of_origin' => $attributes['country_of_origin'] ?? null,
            'lc_code_number' => $attributes['lc_code_number'] ?? null,
            'delivery_to_dipo' => $attributes['delivery_to_dipo'] ?? null,
            'delivery_date' => $attributes['delivery_date'] ?? null,
            'color' => $attributes['color'] ?? null,
            'financed_by' => $attributes['financed_by'] ?? null,
            'tennure_of_the_terms' => $attributes['tennure_of_the_terms'] ?? null,
            'status' => 1,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a bus by ID or throw ModelNotFoundException.
     *
     * @param  int  $id
     * @return Bus
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Bus
    {
        $bus = Bus::find($id);

        if (! $bus) {
            throw new ModelNotFoundException("Bus with ID {$id} not found.");
        }

        return $bus;
    }

    /**
     * Update the specified bus inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Bus
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Bus
    {
        return DB::transaction(function () use ($id, $attributes) {
            $bus = $this->findById($id);

            $bus->update([
                'registration_number' => $attributes['registration_number'],
                'manufacturer_company' => $attributes['manufacturer_company'],
                'model_year' => $attributes['model_year'],
                'chasis_no' => $attributes['chasis_no'],
                'engine_number' => $attributes['engine_number'],
                'country_of_origin' => $attributes['country_of_origin'] ?? $bus->country_of_origin,
                'lc_code_number' => $attributes['lc_code_number'] ?? $bus->lc_code_number,
                'delivery_to_dipo' => $attributes['delivery_to_dipo'] ?? $bus->delivery_to_dipo,
                'delivery_date' => $attributes['delivery_date'] ?? $bus->delivery_date,
                'color' => $attributes['color'] ?? $bus->color,
                'financed_by' => $attributes['financed_by'] ?? $bus->financed_by,
                'tennure_of_the_terms' => $attributes['tennure_of_the_terms'] ?? $bus->tennure_of_the_terms,
                'status' => $attributes['status'] ?? $bus->status,
                'updated_by' => auth()->id(),
            ]);

            return $bus->fresh();
        });
    }

    /**
     * Soft-delete the specified bus inside a database transaction.
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
