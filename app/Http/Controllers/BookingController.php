<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Booking\BookingIndexRequest;
use App\Http\Requests\Api\Booking\BookingShowRequest;
use App\Http\Requests\Api\Booking\BookingStoreRequest;
use App\Http\Requests\Api\Booking\BookingUpdateRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BookingService $bookingService) {}

    /**
     * Display a paginated listing of all bookings.
     */
    public function index(BookingIndexRequest $request): JsonResponse
    {
        $bookings = $this->bookingService->pagination($request->validated());

        return $this->successResponse(
            BookingResource::collection($bookings)->resolve(),
            'Bookings retrieved successfully',
            $this->preparePaginator($bookings)
        );
    }

    /**
     * Store a newly created booking.
     */
    public function store(BookingStoreRequest $request): JsonResponse
    {
        $booking = $this->bookingService->store($request->validated());

        return $this->createdResponse(new BookingResource($booking), 'Booking created successfully');
    }

    /**
     * Display the specified booking.
     */
    public function show(BookingShowRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->findById($id);

        return $this->successResponse(new BookingResource($booking), 'Booking retrieved successfully');
    }

    /**
     * Update the specified booking.
     */
    public function update(BookingUpdateRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->update($id, $request->validated());

        return $this->successResponse(new BookingResource($booking), 'Booking updated successfully');
    }
}
