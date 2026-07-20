<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Faq\FaqDestroyRequest;
use App\Http\Requests\Api\Faq\FaqIndexRequest;
use App\Http\Requests\Api\Faq\FaqShowRequest;
use App\Http\Requests\Api\Faq\FaqStoreRequest;
use App\Http\Requests\Api\Faq\FaqUpdateRequest;
use App\Http\Resources\FaqResource;
use App\Services\FaqService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FaqService $faqService) {}

    /**
     * Display a paginated listing of all faqs.
     *
     * @param  FaqIndexRequest  $request
     * @return JsonResponse
     */
    public function index(FaqIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $faqs = $this->faqService->pagination($attributes);

        return $this->successResponse(
            FaqResource::collection($faqs)->resolve(),
            'Faqs retrieved successfully',
            $this->preparePaginator($faqs)
        );
    }

    /**
     * Display all active without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $faqs = $this->faqService->allActive();

        return $this->successResponse(
            FaqResource::collection($faqs)->resolve(),
            'All active faqs retrieved successfully'
        );
    }

    /**
     * Store a newly created faq.
     *
     * @param  FaqStoreRequest  $request
     * @return JsonResponse
     */
    public function store(FaqStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $faq = $this->faqService->store($attributes);

        return $this->createdResponse(new FaqResource($faq), 'Faq created successfully');
    }

    /**
     * Display the specified faq.
     *
     * @param  FaqShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(FaqShowRequest $request, int $id): JsonResponse
    {
        $faq = $this->faqService->findById($id);

        return $this->successResponse(new FaqResource($faq), 'Faq retrieved successfully');
    }

    /**
     * Update the specified faq.
     *
     * @param  FaqUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(FaqUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $faq = $this->faqService->update($id, $attributes);

        return $this->successResponse(new FaqResource($faq), 'Faq updated successfully');
    }

    /**
     * Set the specified faq to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $faq = $this->faqService->activeById($id);

        return $this->successResponse(new FaqResource($faq), 'Faq activated successfully');
    }

    /**
     * Set the specified faq to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $faq = $this->faqService->inactiveById($id);

        return $this->successResponse(new FaqResource($faq), 'Faq deactivated successfully');
    }

    /**
     * Remove the specified faq.
     *
     * @param  FaqDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(FaqDestroyRequest $request, int $id): JsonResponse
    {
        $this->faqService->destroy($id);

        return $this->successResponse([], 'Faq deleted successfully');
    }
}
