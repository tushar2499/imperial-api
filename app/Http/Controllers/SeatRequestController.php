<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\SeatRequest\SeatBookBlockCancelRequest;
use App\Http\Requests\Api\SeatRequest\SeatBookBlockStoreRequest;
use App\Http\Requests\Api\SeatRequest\SeatRequestCancelIssueRequest;
use App\Http\Requests\Api\SeatRequest\SeatRequestCancelRequest;
use App\Http\Requests\Api\SeatRequest\SeatRequestStoreRequest;
use App\Http\Resources\SeatRequestResource;
use App\Models\SeatInventory;
use App\Services\SeatRequestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SeatRequestController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SeatRequestService $seatRequestService) {}

    /**
     * Create a pending seat request, blocking the seat for 5 minutes.
     *
     * @param  SeatRequestStoreRequest  $request
     * @return JsonResponse
     */
    public function seatRequest(SeatRequestStoreRequest $request): JsonResponse
    {
        $data = $this->seatRequestService->store($request->validated(), auth()->id());

        return $this->createdResponse(new SeatRequestResource($data), 'Seat blocked successfully for 5 minutes');
    }

    /**
     * Directly book (status=2) or block (status=3) a seat without a pending hold.
     *
     * @param  SeatBookBlockStoreRequest  $request
     * @return JsonResponse
     */
    public function seatBookBlockRequest(SeatBookBlockStoreRequest $request): JsonResponse
    {
        $data = $this->seatRequestService->bookOrBlock($request->validated(), auth()->id());

        $message = $request->integer('status') === SeatInventory::STATUS_BOOKED
            ? 'Seat booked successfully until 1 hour before departure'
            : 'Seat blocked successfully';

        return $this->createdResponse(new SeatRequestResource($data), $message);
    }

    /**
     * Cancel a booked or blocked seat request and release the seat back to available.
     *
     * @param  SeatBookBlockCancelRequest  $request
     * @return JsonResponse
     */
    public function seatBookBlockCancel(SeatBookBlockCancelRequest $request): JsonResponse
    {
        $data = $this->seatRequestService->cancelBookBlock($request->validated(), auth()->id());

        return $this->successResponse(new SeatRequestResource($data), 'Seat request cancelled successfully');
    }

    /**
     * Cancel a single pending seat request within an issue and release the seat.
     *
     * @param  SeatRequestCancelRequest  $request
     * @return JsonResponse
     */
    public function removeSeatRequest(SeatRequestCancelRequest $request): JsonResponse
    {
        $data = $this->seatRequestService->cancel($request->validated(), auth()->id());

        return $this->successResponse(new SeatRequestResource($data), 'Seat request cancelled successfully');
    }

    /**
     * Cancel all pending seat requests belonging to an issue and release all seats.
     *
     * @param  SeatRequestCancelIssueRequest  $request
     * @return JsonResponse
     */
    public function removeAllSeatsFromIssue(SeatRequestCancelIssueRequest $request): JsonResponse
    {
        $data = $this->seatRequestService->cancelIssue($request->validated(), auth()->id());

        return $this->successResponse(new SeatRequestResource($data), 'All seats cancelled from issue successfully');
    }
}
