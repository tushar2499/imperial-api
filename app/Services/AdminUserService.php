<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminUserService
{
    /**
     * Get a paginated listing of admin users with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new admin user inside a database transaction.
     *
     * @param  array  $attributes
     * @return User
     */
    public function store(array $attributes): User
    {
        return DB::transaction(function () use ($attributes) {
            return User::create([
                'user_name' => $attributes['user_name'],
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'] ?? null,
                'mobile' => $attributes['mobile'] ?? null,
                'email' => $attributes['email'] ?? null,
                'type' => $attributes['type'],
                'gender' => $attributes['gender'] ?? null,
                'district_id' => $attributes['district_id'] ?? null,
                'date_of_birth' => $attributes['date_of_birth'] ?? null,
                'role' => $attributes['role'] ?? null,
                'counter_id' => $attributes['type'] == 2 ? ($attributes['counter_id'] ?? null) : null,
                'access_type' => $attributes['access_type'],
                'account_type' => $attributes['account_type'] ?? null,
                'front_date' => $attributes['front_date'] ?? null,
                'back_date' => $attributes['back_date'] ?? null,
                'identity_type' => $attributes['identity_type'] ?? null,
                'identity_image' => $this->handleIdentityImage($attributes),
                'sales_status' => $attributes['sales_status'] ?? null,
                'booking_status' => $attributes['booking_status'] ?? null,
                'block_status' => $attributes['block_status'] ?? null,
                'cancel_status' => $attributes['cancel_status'] ?? null,
                'password' => bcrypt($attributes['password'] ?? '123456'),
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Find an admin user by ID or throw ModelNotFoundException.
     *
     * @param  int  $id
     * @return User
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): User
    {
        $user = User::find($id);

        if (! $user) {
            throw new ModelNotFoundException("Admin user with ID {$id} not found.");
        }

        return $user;
    }

    /**
     * Update the specified admin user inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return User
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): User
    {
        return DB::transaction(function () use ($id, $attributes) {
            $user = $this->findById($id);

            $data = [
                'user_name' => $attributes['user_name'],
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'] ?? null,
                'mobile' => $attributes['mobile'] ?? null,
                'email' => $attributes['email'] ?? null,
                'type' => $attributes['type'],
                'gender' => $attributes['gender'] ?? null,
                'district_id' => $attributes['district_id'] ?? null,
                'date_of_birth' => $attributes['date_of_birth'] ?? null,
                'role' => $attributes['role'] ?? null,
                'counter_id' => $attributes['type'] == 2 ? ($attributes['counter_id'] ?? null) : null,
                'access_type' => $attributes['access_type'],
                'account_type' => $attributes['account_type'] ?? null,
                'front_date' => $attributes['front_date'] ?? null,
                'back_date' => $attributes['back_date'] ?? null,
                'identity_type' => $attributes['identity_type'] ?? null,
                'identity_image' => $this->handleIdentityImage($attributes),
                'sales_status' => $attributes['sales_status'] ?? null,
                'booking_status' => $attributes['booking_status'] ?? null,
                'block_status' => $attributes['block_status'] ?? null,
                'cancel_status' => $attributes['cancel_status'] ?? null,
                'updated_by' => auth()->id(),
            ];

            if (! empty($attributes['password'])) {
                $data['password'] = bcrypt($attributes['password']);
            }

            $user->update($data);

            return $user->fresh();
        });
    }

    /**
     * Soft-delete the specified admin user inside a database transaction.
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

    /**
     * Resolve the identity image path from request attributes.
     *
     * @param  array  $attributes
     * @return string|null
     */
    private function handleIdentityImage(array $attributes): ?string
    {
        if (! empty($attributes['identity_type']) && ($attributes['identity_image'] ?? null) instanceof UploadedFile) {
            return file_uploaded($attributes['identity_image'], 'users/identity');
        }

        return null;
    }
}
