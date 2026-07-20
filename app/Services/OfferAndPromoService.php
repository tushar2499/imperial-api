<?php

namespace App\Services;

use App\Models\OfferAndPromo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OfferAndPromoService
{
    /**
     * Get a paginated listing of offer and promos with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = OfferAndPromo::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('expired_date', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active offer and promos without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return OfferAndPromo::where('status', 1)->latest()->get();
    }

    /**
     * Create a new offer and promo inside a database transaction.
     *
     * @param  array  $attributes
     * @return OfferAndPromo
     */
    public function store(array $attributes): OfferAndPromo
    {
        $imagePath = file_uploaded($attributes['image'], 'offer-and-promos');

        return DB::transaction(fn () => OfferAndPromo::create([
            'title' => $attributes['title'],
            'expired_date' => $attributes['expired_date'] ?? null,
            'description' => $attributes['description'] ?? null,
            'link' => $attributes['link'] ?? null,
            'image' => $imagePath,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find an offer and promo by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return OfferAndPromo
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): OfferAndPromo
    {
        $offerAndPromo = OfferAndPromo::find($id);

        if (! $offerAndPromo) {
            throw new ModelNotFoundException("Offer and promo with ID {$id} not found.");
        }

        return $offerAndPromo;
    }

    /**
     * Update the specified offer and promo inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return OfferAndPromo
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): OfferAndPromo
    {
        return DB::transaction(function () use ($id, $attributes) {
            $offerAndPromo = $this->findById($id);

            $imagePath = $offerAndPromo->image;

            if (isset($attributes['image'])) {
                $newPath = file_uploaded($attributes['image'], 'offer-and-promos');

                if ($newPath) {
                    delete_uploaded_file($offerAndPromo->image);
                    $imagePath = $newPath;
                }
            }

            $offerAndPromo->update([
                'title' => $attributes['title'],
                'expired_date' => $attributes['expired_date'] ?? null,
                'description' => $attributes['description'] ?? null,
                'link' => $attributes['link'] ?? null,
                'image' => $imagePath,
                'updated_by' => auth()->id(),
            ]);

            return $offerAndPromo->fresh();
        });
    }

    /**
     * Set the offer and promo status to active (1).
     *
     * @param  int  $id
     * @return OfferAndPromo
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): OfferAndPromo
    {
        return DB::transaction(function () use ($id) {
            $offerAndPromo = $this->findById($id);
            $offerAndPromo->update(['status' => 1]);

            return $offerAndPromo->fresh();
        });
    }

    /**
     * Set the offer and promo status to inactive (0).
     *
     * @param  int  $id
     * @return OfferAndPromo
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): OfferAndPromo
    {
        return DB::transaction(function () use ($id) {
            $offerAndPromo = $this->findById($id);
            $offerAndPromo->update(['status' => 0]);

            return $offerAndPromo->fresh();
        });
    }

    /**
     * Delete the specified offer and promo and its image inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $offerAndPromo = $this->findById($id);
            delete_uploaded_file($offerAndPromo->image);
            $offerAndPromo->delete();
        });
    }
}
