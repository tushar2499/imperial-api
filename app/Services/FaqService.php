<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FaqService
{
    /**
     * Get a paginated listing of faqs with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Faq::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active faqs without pagination.
     *
     * @return Collection
     */
    public function allActive(): Collection
    {
        return Faq::where('status', 1)->latest()->get();
    }

    /**
     * Create a new faq inside a database transaction.
     *
     * @param  array  $attributes
     * @return Faq
     */
    public function store(array $attributes): Faq
    {
        return DB::transaction(fn () => Faq::create([
            'question' => $attributes['question'],
            'answer' => $attributes['answer'] ?? null,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a faq by its ID or throw a ModelNotFoundException.
     *
     * @param  int  $id
     * @return Faq
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Faq
    {
        $faq = Faq::find($id);

        if (! $faq) {
            throw new ModelNotFoundException("Faq with ID {$id} not found.");
        }

        return $faq;
    }

    /**
     * Update the specified faq inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Faq
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Faq
    {
        return DB::transaction(function () use ($id, $attributes) {
            $faq = $this->findById($id);

            $faq->update([
                'question' => $attributes['question'],
                'answer' => $attributes['answer'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            return $faq->fresh();
        });
    }

    /**
     * Set the faq status to active (1).
     *
     * @param  int  $id
     * @return Faq
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): Faq
    {
        return DB::transaction(function () use ($id) {
            $faq = $this->findById($id);
            $faq->update(['status' => 1]);

            return $faq->fresh();
        });
    }

    /**
     * Set the faq status to inactive (0).
     *
     * @param  int  $id
     * @return Faq
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): Faq
    {
        return DB::transaction(function () use ($id) {
            $faq = $this->findById($id);
            $faq->update(['status' => 0]);

            return $faq->fresh();
        });
    }

    /**
     * Delete the specified faq inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $faq = $this->findById($id);
            $faq->delete();
        });
    }
}
