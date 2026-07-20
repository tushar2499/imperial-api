<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\OfferAndPromo\OfferAndPromoDestroyRequest;
use App\Http\Requests\Api\OfferAndPromo\OfferAndPromoIndexRequest;
use App\Http\Requests\Api\OfferAndPromo\OfferAndPromoShowRequest;
use App\Http\Requests\Api\OfferAndPromo\OfferAndPromoStoreRequest;
use App\Http\Requests\Api\OfferAndPromo\OfferAndPromoUpdateRequest;
use App\Http\Resources\OfferAndPromoResource;
use App\Services\OfferAndPromoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OfferAndPromoController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OfferAndPromoService $offerAndPromoService) {}

    /**
     * Display a paginated listing of all offer and promos.
     *
     * @param  OfferAndPromoIndexRequest  $request
     * @return JsonResponse
     */
    public function index(OfferAndPromoIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $offerAndPromos = $this->offerAndPromoService->pagination($attributes);

        return $this->successResponse(
            OfferAndPromoResource::collection($offerAndPromos)->resolve(),
            'Offer and promos retrieved successfully',
            $this->preparePaginator($offerAndPromos)
        );
    }

    /**
     * Display all active without pagination.
     *
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $offerAndPromos = $this->offerAndPromoService->allActive();

        return $this->successResponse(
            OfferAndPromoResource::collection($offerAndPromos)->resolve(),
            'All active offer and promos retrieved successfully'
        );
    }

    /**
     * Store a newly created offer and promo.
     *
     * @param  OfferAndPromoStoreRequest  $request
     * @return JsonResponse
     */
    public function store(OfferAndPromoStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $offerAndPromo = $this->offerAndPromoService->store($attributes);

        return $this->createdResponse(new OfferAndPromoResource($offerAndPromo), 'Offer and promo created successfully');
    }

    /**
     * Display the specified offer and promo.
     *
     * @param  OfferAndPromoShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(OfferAndPromoShowRequest $request, int $id): JsonResponse
    {
        $offerAndPromo = $this->offerAndPromoService->findById($id);

        return $this->successResponse(new OfferAndPromoResource($offerAndPromo), 'Offer and promo retrieved successfully');
    }

    /**
     * Update the specified offer and promo.
     *
     * @param  OfferAndPromoUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(OfferAndPromoUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $offerAndPromo = $this->offerAndPromoService->update($id, $attributes);

        return $this->successResponse(new OfferAndPromoResource($offerAndPromo), 'Offer and promo updated successfully');
    }

    /**
     * Set the specified offer and promo to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $offerAndPromo = $this->offerAndPromoService->activeById($id);

        return $this->successResponse(new OfferAndPromoResource($offerAndPromo), 'Offer and promo activated successfully');
    }

    /**
     * Set the specified offer and promo to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $offerAndPromo = $this->offerAndPromoService->inactiveById($id);

        return $this->successResponse(new OfferAndPromoResource($offerAndPromo), 'Offer and promo deactivated successfully');
    }

    /**
     * Remove the specified offer and promo.
     *
     * @param  OfferAndPromoDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(OfferAndPromoDestroyRequest $request, int $id): JsonResponse
    {
        $this->offerAndPromoService->destroy($id);

        return $this->successResponse([], 'Offer and promo deleted successfully');
    }
}
