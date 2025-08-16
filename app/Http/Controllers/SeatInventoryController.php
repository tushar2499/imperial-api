<?php

namespace App\Http\Controllers;

use App\Models\SeatInventory;
use App\Services\SeatInventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeatInventoryController extends Controller
{
    use ApiResponse;

    protected $seatInventoryService;

    public function __construct(SeatInventoryService $seatInventoryService)
    {
        $this->seatInventoryService = $seatInventoryService;
    }

    /**
     * Get seat inventory for a trip
     *
     * @param int $tripId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTripSeats($tripId, Request $request)
    {
        try {
            $filters = [];

            if ($request->filled('booking_status')) {
                $filters['booking_status'] = $request->booking_status;
            }

            if ($request->filled('seat_type')) {
                $filters['seat_type'] = $request->seat_type;
            }

            $result = $this->seatInventoryService->getTripSeatInventory($tripId, $filters);

            if ($result['success']) {
                return $this->successResponse($result['data'], 'Seat inventory retrieved successfully');
            } else {
                return $this->errorResponse($result['message'], 404);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve seat inventory: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create seat inventory for a trip
     *
     * @param int $tripId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTripSeats($tripId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seat_plan_id' => 'nullable|exists:seat_plans,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $seatPlanId = $request->input('seat_plan_id');
            $result = $this->seatInventoryService->createSeatInventoryForTrip($tripId, $seatPlanId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message'], 201);
            } else {
                return $this->errorResponse($result['message'], 422);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create seat inventory: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Block a seat temporarily
     *
     * @param int $tripId
     * @param int $seatId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function blockSeat($tripId, $seatId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'minutes' => 'sometimes|integer|min:1|max:120',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $minutes = $request->input('minutes', 15);
            $userId = $request->input('user_id');

            $result = $this->seatInventoryService->blockSeat($tripId, $seatId, $minutes, $userId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 422);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to block seat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Book a seat
     *
     * @param int $tripId
     * @param int $seatId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookSeat($tripId, $seatId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $bookingId = $request->input('booking_id');
            $userId = $request->input('user_id');

            $result = $this->seatInventoryService->bookSeat($tripId, $seatId, $bookingId, $userId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 422);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to book seat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Release a seat (make it available)
     *
     * @param int $tripId
     * @param int $seatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function releaseSeat($tripId, $seatId)
    {
        try {
            $result = $this->seatInventoryService->releaseSeat($tripId, $seatId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 422);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to release seat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel a seat booking
     *
     * @param int $tripId
     * @param int $seatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSeat($tripId, $seatId)
    {
        try {
            $result = $this->seatInventoryService->cancelSeat($tripId, $seatId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 422);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to cancel seat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk update seat statuses
     *
     * @param int $tripId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateSeats($tripId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seats' => 'required|array|min:1',
            'seats.*.seat_id' => 'required|exists:seats,id',
            'seats.*.action' => 'required|in:block,book,release,cancel',
            'seats.*.booking_id' => 'required_if:seats.*.action,book|nullable|exists:bookings,id',
            'seats.*.minutes' => 'sometimes|integer|min:1|max:120',
            'seats.*.user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($request->input('seats') as $seatData) {
                $seatId = $seatData['seat_id'];
                $action = $seatData['action'];

                try {
                    switch ($action) {
                        case 'block':
                            $minutes = $seatData['minutes'] ?? 15;
                            $userId = $seatData['user_id'] ?? null;
                            $result = $this->seatInventoryService->blockSeat($tripId, $seatId, $minutes, $userId);
                            break;

                        case 'book':
                            $bookingId = $seatData['booking_id'];
                            $userId = $seatData['user_id'] ?? null;
                            $result = $this->seatInventoryService->bookSeat($tripId, $seatId, $bookingId, $userId);
                            break;

                        case 'release':
                            $result = $this->seatInventoryService->releaseSeat($tripId, $seatId);
                            break;

                        case 'cancel':
                            $result = $this->seatInventoryService->cancelSeat($tripId, $seatId);
                            break;

                        default:
                            $result = ['success' => false, 'message' => 'Invalid action'];
                    }

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }

                    $results[] = [
                        'seat_id' => $seatId,
                        'action' => $action,
                        'success' => $result['success'],
                        'message' => $result['message']
                    ];

                } catch (\Exception $e) {
                    $errorCount++;
                    $results[] = [
                        'seat_id' => $seatId,
                        'action' => $action,
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return $this->successResponse([
                'trip_id' => $tripId,
                'summary' => [
                    'total_seats' => count($request->input('seats')),
                    'successful' => $successCount,
                    'failed' => $errorCount
                ],
                'results' => $results
            ], "Bulk update completed: {$successCount} successful, {$errorCount} failed");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to perform bulk update: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Clean up expired seat blocks
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanupExpiredBlocks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $tripId = $request->input('trip_id');
            $result = $this->seatInventoryService->cleanupExpiredBlocks($tripId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 422);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to cleanup expired blocks: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get seat availability summary
     *
     * @param int $tripId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSeatAvailability($tripId)
    {
        try {
            $result = $this->seatInventoryService->getTripSeatInventory($tripId);

            if ($result['success']) {
                // Return only the summary
                $summaryData = [
                    'trip_id' => $tripId,
                    'availability' => $result['data']['summary']
                ];

                return $this->successResponse($summaryData, 'Seat availability retrieved successfully');
            } else {
                return $this->errorResponse($result['message'], 404);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve seat availability: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get partition information for seat inventories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPartitionInfo()
    {
        try {
            $seatInventory = new SeatInventory();
            $allPartitions = $seatInventory->getAllPartitionTables();

            $statistics = [];
            $totalRecords = 0;
            $totalSizeMB = 0;

            foreach ($allPartitions as $partition) {
                try {
                    $count = DB::table($partition)->count();
                    $size = DB::select("
                        SELECT
                            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                        FROM information_schema.tables
                        WHERE table_schema = DATABASE()
                        AND table_name = ?
                    ", [$partition])[0]->size_mb ?? 0;

                    // Extract month from table name
                    $month = str_replace('seat_inventories_', '', $partition);
                    if (strlen($month) === 6 && is_numeric($month)) {
                        $formattedMonth = \Carbon\Carbon::createFromFormat('Ym', $month)->format('Y-m');
                    } else {
                        $formattedMonth = 'Unknown';
                    }

                    $statistics[] = [
                        'table' => $partition,
                        'month' => $formattedMonth,
                        'record_count' => $count,
                        'size_mb' => (float) $size
                    ];

                    $totalRecords += $count;
                    $totalSizeMB += (float) $size;
                } catch (\Exception $e) {
                    // Skip if table query fails
                    continue;
                }
            }

            // Sort statistics by month
            usort($statistics, function($a, $b) {
                return strcmp($a['month'], $b['month']);
            });

            return $this->successResponse([
                'total_partitions' => count($allPartitions),
                'total_records' => $totalRecords,
                'total_size_mb' => round($totalSizeMB, 2),
                'partitions' => $statistics
            ], 'Seat inventory partition information retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve partition information: ' . $e->getMessage(), 500);
        }
    }
}
