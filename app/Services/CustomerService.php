<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * Get a paginated listing of customers with optional search.
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Customer::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active customers without pagination.
     */
    public function allActive(): Collection
    {
        return Customer::where('status', 1)->latest()->get();
    }

    /**
     * Create a new customer inside a database transaction.
     */
    public function store(array $attributes): Customer
    {
        return DB::transaction(fn () => Customer::create([
            'mobile' => $attributes['mobile'],
            'name' => $attributes['name'],
            'gender' => $attributes['gender'] ?? null,
            'age' => $attributes['age'] ?? null,
            'address' => $attributes['address'] ?? null,
            'passport_no' => $attributes['passport_no'] ?? null,
            'nid' => $attributes['nid'] ?? null,
            'nationality' => $attributes['nationality'] ?? null,
            'email' => $attributes['email'] ?? null,
            'status' => $attributes['status'] ?? 1,
        ]));
    }

    /**
     * Find a customer by ID or throw ModelNotFoundException.
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Customer
    {
        $customer = Customer::find($id);

        if (! $customer) {
            throw new ModelNotFoundException("Customer with ID {$id} not found.");
        }

        return $customer;
    }

    /**
     * Find a customer by mobile number or throw ModelNotFoundException.
     *
     * @throws ModelNotFoundException
     */
    public function findByMobile(string $mobile): Customer
    {
        $customer = Customer::where('mobile', $mobile)->first();

        if (! $customer) {
            throw new ModelNotFoundException("Customer with mobile {$mobile} not found.");
        }

        return $customer;
    }

    /**
     * Update the specified customer inside a database transaction.
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Customer
    {
        return DB::transaction(function () use ($id, $attributes) {
            $customer = $this->findById($id);

            $customer->update([
                'mobile' => $attributes['mobile'],
                'name' => $attributes['name'],
                'gender' => $attributes['gender'] ?? $customer->gender,
                'age' => $attributes['age'] ?? $customer->age,
                'address' => $attributes['address'] ?? $customer->address,
                'passport_no' => $attributes['passport_no'] ?? $customer->passport_no,
                'nid' => $attributes['nid'] ?? $customer->nid,
                'nationality' => $attributes['nationality'] ?? $customer->nationality,
                'email' => $attributes['email'] ?? $customer->email,
                'status' => $attributes['status'] ?? $customer->status,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Update the specified customer by mobile inside a database transaction.
     *
     * @throws ModelNotFoundException
     */
    public function updateByMobile(string $mobile, array $attributes): Customer
    {
        return DB::transaction(function () use ($mobile, $attributes) {
            $customer = $this->findByMobile($mobile);

            $customer->update([
                'name' => $attributes['name'],
                'gender' => $attributes['gender'] ?? $customer->gender,
                'age' => $attributes['age'] ?? $customer->age,
                'address' => $attributes['address'] ?? $customer->address,
                'passport_no' => $attributes['passport_no'] ?? $customer->passport_no,
                'nationality' => $attributes['nationality'] ?? $customer->nationality,
                'email' => $attributes['email'] ?? $customer->email,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Set the customer status to active (1).
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): Customer
    {
        return DB::transaction(function () use ($id) {
            $customer = $this->findById($id);
            $customer->update(['status' => 1]);

            return $customer->fresh();
        });
    }

    /**
     * Set the customer status to inactive (0).
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): Customer
    {
        return DB::transaction(function () use ($id) {
            $customer = $this->findById($id);
            $customer->update(['status' => 0]);

            return $customer->fresh();
        });
    }

    /**
     * Delete the specified customer inside a database transaction.
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
