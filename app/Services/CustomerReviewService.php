<?php

namespace App\Services;

use App\Models\CustomerReview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerReviewService
{
    /**
     * Get a paginated listing of customer reviews with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = CustomerReview::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active customer reviews without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return CustomerReview::where('status', 1)->latest()->get();
    }

    /**
     * Create a new customer review inside a database transaction.
     *
     * @param  array  $attributes
     * @return CustomerReview
     */
    public function store(array $attributes): CustomerReview
    {
        $imagePath = isset($attributes['image']) ? file_uploaded($attributes['image'], 'customer-reviews') : null;

        return DB::transaction(fn () => CustomerReview::create([
            'name' => $attributes['name'],
            'date' => $attributes['date'],
            'comment' => $attributes['comment'],
            'rating' => $attributes['rating'] ?? null,
            'image' => $imagePath,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a customer review by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return CustomerReview
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): CustomerReview
    {
        $customerReview = CustomerReview::find($id);

        if (! $customerReview) {
            throw new ModelNotFoundException("Customer review with ID {$id} not found.");
        }

        return $customerReview;
    }

    /**
     * Update the specified customer review inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return CustomerReview
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): CustomerReview
    {
        return DB::transaction(function () use ($id, $attributes) {
            $customerReview = $this->findById($id);

            $imagePath = $customerReview->image;

            if (isset($attributes['image'])) {
                $newPath = file_uploaded($attributes['image'], 'customer-reviews');

                if ($newPath) {
                    delete_uploaded_file($customerReview->image);
                    $imagePath = $newPath;
                }
            }

            $customerReview->update([
                'name' => $attributes['name'],
                'date' => $attributes['date'],
                'comment' => $attributes['comment'],
                'rating' => $attributes['rating'] ?? null,
                'image' => $imagePath,
                'updated_by' => auth()->id(),
            ]);

            return $customerReview->fresh();
        });
    }

    /**
     * Set the customer review status to active (1).
     *
     * @param  int  $id
     * @return CustomerReview
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): CustomerReview
    {
        return DB::transaction(function () use ($id) {
            $customerReview = $this->findById($id);
            $customerReview->update(['status' => 1]);

            return $customerReview->fresh();
        });
    }

    /**
     * Set the customer review status to inactive (0).
     *
     * @param  int  $id
     * @return CustomerReview
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): CustomerReview
    {
        return DB::transaction(function () use ($id) {
            $customerReview = $this->findById($id);
            $customerReview->update(['status' => 0]);

            return $customerReview->fresh();
        });
    }

    /**
     * Delete the specified customer review and its image inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $customerReview = $this->findById($id);
            delete_uploaded_file($customerReview->image);
            $customerReview->delete();
        });
    }
}
