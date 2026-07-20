<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Seat;
use App\Models\SeatInventory;
use App\Models\SeatRequest;
use App\Models\TripInstance;
use App\Services\GuestSeatHoldService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Sslcommerz\Laravel\Contracts\PaymentGatewayInterface;
use Sslcommerz\Laravel\DTOs\PaymentRequestDTO;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PaymentGatewayInterface $sslcommerz,
        private readonly GuestSeatHoldService $guestSeatHoldService,
    ) {}

    /**
     * Confirm a payment after SSLCommerz callback.
     * Called by the Next.js API route handler (server-to-server, no user auth needed).
     * Validates the val_id with SSLCommerz, confirms the seat hold booking, returns JSON.
     */
    public function confirm(Request $request): JsonResponse
    {
        $tranId = $request->input('tran_id', '');
        $valId  = $request->input('val_id', '');

        if (! $tranId || ! $valId) {
            return $this->errorResponse('Missing tran_id or val_id', 422);
        }

        try {
            $validation = $this->sslcommerz->validate($valId);
        } catch (\Exception $e) {
            Log::error('SSLCommerz validation error', ['tran_id' => $tranId, 'error' => $e->getMessage()]);
            return $this->errorResponse('Payment validation failed', 500);
        }

        if (! $validation->isSuccessful()) {
            return $this->errorResponse('Payment not validated: ' . $validation->status, 400);
        }

        $initData = Cache::get("payment_init_{$tranId}");
        if (! $initData) {
            return $this->errorResponse('Booking session expired or not found', 410);
        }

        try {
            $result = $this->guestSeatHoldService->confirm(
                $initData['issue_id'],
                $initData['guest_token'],
                $initData['user_id'],
                $initData['boarding_id'],
                $initData['dropping_id'],
            );

            Booking::where('id', $result['booking_id'])->update([
                'payment_method' => 'sslcommerz',
                'payment_status' => 'paid',
                'tran_id'        => $tranId,
            ]);

            Cache::forget("payment_init_{$tranId}");

            return $this->successResponse([
                'pnr_number'   => $result['pnr_number'],
                'booking_id'   => $result['booking_id'],
                'total_amount' => $result['total_amount'],
                'tran_id'      => $tranId,
            ], 'Booking confirmed successfully');
        } catch (\Exception $e) {
            Log::error('SSLCommerz booking confirm failed', ['tran_id' => $tranId, 'error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Initiate an SSLCommerz payment session.
     * Auth required. Claims the guest seat hold, calculates fare, and redirects to SSLCommerz.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'issue_id'    => 'required|string',
            'guest_token' => 'required|string',
            'boarding_id' => 'required|integer',
            'dropping_id' => 'required|integer',
        ]);

        $user = auth()->user();

        // Find pending seat hold by issue_id + guest_token (user_id not set yet — claim happens below)
        $seatRequests = SeatRequest::where('issue_id', $validated['issue_id'])
            ->where('guest_token', $validated['guest_token'])
            ->where('status', 'pending')
            ->get();

        if ($seatRequests->isEmpty()) {
            return $this->errorResponse(
                'No active seat hold found. Your hold may have expired — please reselect seats.',
                404
            );
        }

        // Claim: associate authenticated user with the seat hold
        $seatRequests->each(fn ($r) => $r->update(['user_id' => $user->id]));

        $tripId = $seatRequests->first()->trip_id;

        // Calculate total fare using the same approach as GuestSeatHoldService::confirm()
        $totalAmount = $this->calculateTotalFare($seatRequests, $tripId);

        // SSLCommerz rejects 0-amount; use 1 as floor (should not happen in production)
        if ($totalAmount <= 0) {
            $totalAmount = 1;
        }

        $tranId = 'IMP_' . strtoupper(substr($validated['issue_id'], -6)) . '_' . time();

        // Derive frontend base from the request Origin header before caching —
        // stored so legacy fallback handlers can redirect without FRONTEND_URL env.
        $frontendBase = rtrim($request->header('Origin', ''), '/');

        // Cache initiation data for 30 minutes to retrieve on success callback
        Cache::put("payment_init_{$tranId}", [
            'issue_id'      => $validated['issue_id'],
            'guest_token'   => $validated['guest_token'],
            'user_id'       => $user->id,
            'boarding_id'   => (int) $validated['boarding_id'],
            'dropping_id'   => (int) $validated['dropping_id'],
            'total_amount'  => $totalAmount,
            'frontend_base' => $frontendBase,
        ], now()->addMinutes(30));

        // Tag seat_requests with tran_id for traceability
        SeatRequest::where('issue_id', $validated['issue_id'])
            ->where('guest_token', $validated['guest_token'])
            ->where('status', 'pending')
            ->update(['tran_id' => $tranId]);

        $backendUrl = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');
        $customerName  = $user->name ?? 'Customer';
        $customerEmail = $user->email ?? 'noreply@imperialexpressbd.com';
        $customerPhone = $user->mobile ?? '01700000000';

        $paymentDto = new PaymentRequestDTO(
            tranId:          $tranId,
            totalAmount:     $totalAmount,
            currency:        'BDT',
            cusName:         $customerName,
            cusEmail:        $customerEmail,
            cusPhone:        $customerPhone,
            cusAdd1:         'Bangladesh',
            cusCity:         'Dhaka',
            cusCountry:      'Bangladesh',
            productName:     'Bus Ticket',
            productCategory: 'Transport',
            productProfile:  'general',
            shippingMethod:  'NO',
            numOfItem:       $seatRequests->count(),
            successUrl:      $frontendBase . '/api/sslcommerz/success',
            failUrl:         $frontendBase . '/api/sslcommerz/fail',
            cancelUrl:       $frontendBase . '/api/sslcommerz/cancel',
            ipnUrl:          $backendUrl   . '/api/payment/ipn',
        );

        $response = $this->sslcommerz->initiate($paymentDto);

        if (! $response->isSuccessful()) {
            Cache::forget("payment_init_{$tranId}");
            Log::error('SSLCommerz initiation failed', [
                'tran_id' => $tranId,
                'reason'  => $response->failedReason,
            ]);
            return $this->errorResponse(
                'Failed to initiate payment: ' . ($response->failedReason ?? 'Unknown error'),
                500
            );
        }

        return $this->successResponse([
            'payment_url' => $response->gatewayPageUrl,
            'sessionkey'  => $response->sessionKey,
            'tran_id'     => $tranId,
        ], 'Payment session initiated successfully');
    }

    /**
     * SSLCommerz success callback — legacy direct handler (primary flow goes via Next.js route handler).
     * Reads frontend_base from the cached init data so no env var is needed.
     */
    public function success(Request $request): RedirectResponse
    {
        $tranId   = $request->input('tran_id', '');
        $valId    = $request->input('val_id', '');
        $initData = $tranId ? Cache::get("payment_init_{$tranId}") : null;
        $base     = rtrim($initData['frontend_base'] ?? '', '/');

        if (! $tranId || ! $valId) {
            return redirect($base . '/payment/failed?reason=' . urlencode('Invalid payment response'));
        }

        try {
            $validation = $this->sslcommerz->validate($valId);
        } catch (\Exception $e) {
            Log::error('SSLCommerz validation error', ['tran_id' => $tranId, 'error' => $e->getMessage()]);
            return redirect($base . '/payment/failed?tran_id=' . $tranId . '&reason=' . urlencode('Payment validation failed'));
        }

        if (! $validation->isSuccessful()) {
            return redirect($base . '/payment/failed?tran_id=' . $tranId . '&reason=' . urlencode($validation->status));
        }

        if (! $initData) {
            return redirect($base . '/payment/failed?tran_id=' . $tranId . '&reason=' . urlencode('Booking session expired'));
        }

        try {
            $result = $this->guestSeatHoldService->confirm(
                $initData['issue_id'],
                $initData['guest_token'],
                $initData['user_id'],
                $initData['boarding_id'],
                $initData['dropping_id'],
            );

            Booking::where('id', $result['booking_id'])->update([
                'payment_method' => 'sslcommerz',
                'payment_status' => 'paid',
                'tran_id'        => $tranId,
            ]);

            Cache::forget("payment_init_{$tranId}");

            return redirect(
                $base . '/payment/success'
                . '?pnr='        . $result['pnr_number']
                . '&booking_id=' . $result['booking_id']
                . '&amount='     . $result['total_amount']
                . '&tran_id='    . $tranId
            );
        } catch (\Exception $e) {
            Log::error('SSLCommerz booking confirm failed', ['tran_id' => $tranId, 'error' => $e->getMessage()]);
            return redirect($base . '/payment/failed?tran_id=' . $tranId . '&reason=' . urlencode($e->getMessage()));
        }
    }

    /**
     * SSLCommerz fail callback — legacy direct handler.
     */
    public function fail(Request $request): RedirectResponse
    {
        $tranId   = $request->input('tran_id', '');
        $error    = $request->input('error', 'Payment processing failed');
        $initData = $tranId ? Cache::get("payment_init_{$tranId}") : null;
        $base     = rtrim($initData['frontend_base'] ?? '', '/');

        return redirect($base . '/payment/failed?tran_id=' . $tranId . '&reason=' . urlencode($error));
    }

    /**
     * SSLCommerz cancel callback — legacy direct handler.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $tranId   = $request->input('tran_id', '');
        $initData = $tranId ? Cache::get("payment_init_{$tranId}") : null;
        $base     = rtrim($initData['frontend_base'] ?? '', '/');

        return redirect($base . '/payment/cancelled?tran_id=' . $tranId);
    }

    /**
     * SSLCommerz IPN — second safety net for payment confirmation. Must return 200.
     */
    public function ipn(Request $request): Response
    {
        $tranId = $request->input('tran_id', '');
        $valId  = $request->input('val_id', '');
        $status = $request->input('status', '');

        if ($tranId && $valId && $status === 'VALID') {
            try {
                $validation = $this->sslcommerz->validate($valId);
                if ($validation->isSuccessful()) {
                    Booking::where('tran_id', $tranId)->update(['payment_status' => 'paid']);
                }
            } catch (\Exception $e) {
                Log::error('SSLCommerz IPN validation failed', ['tran_id' => $tranId, 'error' => $e->getMessage()]);
            }
        }

        return response('OK', 200);
    }

    /**
     * Calculate total fare for seat requests using trip's fare configuration.
     * Mirrors the logic in GuestSeatHoldService::confirm().
     */
    private function calculateTotalFare($seatRequests, int $tripId): float
    {
        try {
            $trip = TripInstance::findAcrossPartitions($tripId);
            if (! $trip) {
                return 0;
            }

            $trip->load('fares');

            $fareMap = $trip->fares
                ->where('coach_type', $trip->coach_type)
                ->where('status', 1)
                ->pluck('amount', 'seat_type');

            $total = 0.0;
            foreach ($seatRequests as $req) {
                $seat  = Seat::find($req->seat_id);
                $total += (float) ($fareMap->get($seat?->seat_type ?? '') ?? 0);
            }

            return $total;
        } catch (\Exception $e) {
            Log::warning('Fare calculation fallback', ['trip_id' => $tripId, 'error' => $e->getMessage()]);
            return 0;
        }
    }
}
