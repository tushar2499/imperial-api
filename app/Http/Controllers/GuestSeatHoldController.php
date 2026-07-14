<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\GuestSeatHold\GuestSeatHoldClaimRequest;
use App\Http\Requests\Api\GuestSeatHold\GuestSeatHoldConfirmRequest;
use App\Http\Requests\Api\GuestSeatHold\GuestSeatHoldReleaseIssueRequest;
use App\Http\Requests\Api\GuestSeatHold\GuestSeatHoldReleaseRequest;
use App\Http\Requests\Api\GuestSeatHold\GuestSeatHoldRequest;
use App\Services\GuestSeatHoldService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GuestSeatHoldController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly GuestSeatHoldService $service) {}

    /**
     * Hold a seat for an anonymous guest (public, no auth required).
     */
    public function hold(GuestSeatHoldRequest $request): JsonResponse
    {
        $data = $this->service->hold(
            $request->validated(),
            $request->validated()['guest_token']
        );

        return $this->createdResponse($data, 'Seat held for '.GuestSeatHoldService::HOLD_MINUTES.' minutes');
    }

    /**
     * Release a single held seat (public, guest must supply their token).
     */
    public function release(GuestSeatHoldReleaseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->service->release($validated, $validated['guest_token']);

        return $this->successResponse($data, 'Seat released successfully');
    }

    /**
     * Release all held seats in an issue (public, guest must supply their token).
     */
    public function releaseIssue(GuestSeatHoldReleaseIssueRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->service->releaseIssue($validated['issue_id'], $validated['guest_token']);

        return $this->successResponse($data, 'All seats released successfully');
    }

    /**
     * Claim a guest hold after login — associates seats with the authenticated user.
     * Requires auth:customer middleware.
     */
    public function claim(GuestSeatHoldClaimRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->service->claim(
            $validated['issue_id'],
            $validated['guest_token'],
            auth()->id()
        );

        return $this->successResponse($data, 'Booking hold claimed successfully');
    }

    /**
     * Confirm a claimed seat hold — creates the Booking record immediately (no payment gateway).
     * Requires auth:customer middleware.
     */
    public function confirm(GuestSeatHoldConfirmRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->service->confirm(
            $validated['issue_id'],
            $validated['guest_token'],
            auth()->id(),
            (int) $validated['boarding_id'],
            (int) $validated['dropping_id'],
        );

        return $this->createdResponse($data, 'Booking confirmed successfully');
    }
}
