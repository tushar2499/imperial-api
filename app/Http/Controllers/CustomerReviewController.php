<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\CustomerReview\CustomerReviewDestroyRequest;
use App\Http\Requests\Api\CustomerReview\CustomerReviewIndexRequest;
use App\Http\Requests\Api\CustomerReview\CustomerReviewShowRequest;
use App\Http\Requests\Api\CustomerReview\CustomerReviewStoreRequest;
use App\Http\Requests\Api\CustomerReview\CustomerReviewUpdateRequest;
use App\Http\Resources\CustomerReviewResource;
use App\Services\CustomerReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomerReviewController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CustomerReviewService $customerReviewService) {}

    /**
     * Display a paginated listing of all customer reviews.
     *
     * @param  CustomerReviewIndexRequest  $request
     * @return JsonResponse
     */
    public function index(CustomerReviewIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $customerReviews = $this->customerReviewService->pagination($attributes);

        return $this->successResponse(
            CustomerReviewResource::collection($customerReviews)->resolve(),
            'Customer reviews retrieved successfully',
            $this->preparePaginator($customerReviews)
        );
    }

    /**
     * Display all active without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $customerReviews = $this->customerReviewService->allActive();

        return $this->successResponse(
            CustomerReviewResource::collection($customerReviews)->resolve(),
            'All active customer reviews retrieved successfully'
        );
    }

    /**
     * Store a newly created customer review.
     *
     * @param  CustomerReviewStoreRequest  $request
     * @return JsonResponse
     */
    public function store(CustomerReviewStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $customerReview = $this->customerReviewService->store($attributes);

        return $this->createdResponse(new CustomerReviewResource($customerReview), 'Customer review created successfully');
    }

    /**
     * Display the specified customer review.
     *
     * @param  CustomerReviewShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(CustomerReviewShowRequest $request, int $id): JsonResponse
    {
        $customerReview = $this->customerReviewService->findById($id);

        return $this->successResponse(new CustomerReviewResource($customerReview), 'Customer review retrieved successfully');
    }

    /**
     * Update the specified customer review.
     *
     * @param  CustomerReviewUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(CustomerReviewUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $customerReview = $this->customerReviewService->update($id, $attributes);

        return $this->successResponse(new CustomerReviewResource($customerReview), 'Customer review updated successfully');
    }

    /**
     * Set the specified customer review to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $customerReview = $this->customerReviewService->activeById($id);

        return $this->successResponse(new CustomerReviewResource($customerReview), 'Customer review activated successfully');
    }

    /**
     * Set the specified customer review to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $customerReview = $this->customerReviewService->inactiveById($id);

        return $this->successResponse(new CustomerReviewResource($customerReview), 'Customer review deactivated successfully');
    }

    /**
     * Remove the specified customer review.
     *
     * @param  CustomerReviewDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(CustomerReviewDestroyRequest $request, int $id): JsonResponse
    {
        $this->customerReviewService->destroy($id);

        return $this->successResponse([], 'Customer review deleted successfully');
    }
}
