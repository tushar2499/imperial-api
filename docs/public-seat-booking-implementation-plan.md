# Public Seat Booking Implementation Plan

**Pattern:** Login-wall at checkout (same as redBus, bdticket, abhibus)  
**Status:** Planned — not yet implemented

---

## Overview

Anyone can browse trips, view seat plans, hold seats, and fill in passenger details — all without logging in. A login/register prompt appears only when the user clicks **"Confirm Booking"**. After login, the booking is confirmed under their account and a PNR is generated.

---

## Current System Analysis

### What already works (no changes needed)
| Feature | Route | Status |
|---|---|---|
| Search trips | `GET /search-trips` | ✅ Public |
| View seat plan | `GET /trip-instances/{id}` | ✅ Public |
| Customer login | `POST /login` | ✅ Public |
| Customer register | `POST /register` | ✅ Public |

### What needs to change
| Feature | Current State | Required State |
|---|---|---|
| Hold seat | Protected (`/admin/seat-requests`) | Public |
| Cancel seat hold | Protected | Public |
| Confirm booking | Protected, no `issue_id` support | Protected + `issue_id` claim |
| `seat_requests.user_id` | `NOT NULL` in DB | Nullable |
| `SeatRequestService` methods | `int $userId` | `?int $userId` |
| Guest ownership check | By `user_id` | By `issue_id` when `user_id` is null |

---

## Booking Flow

```
┌─────────────────────────────────────────────────────────────┐
│  PHASE 1 — GUEST (no login required)                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. Search trips                                            │
│     GET /api/search-trips?trip_date=&route_start_id=&...   │
│                                                             │
│  2. View seat plan                                          │
│     GET /api/trip-instances/{id}                           │
│                                                             │
│  3. Select a seat (5-min hold)                              │
│     POST /api/seat-requests                                 │
│     → returns { issue_id, blocked_until, seat_info }        │
│     → frontend stores issue_id in localStorage             │
│                                                             │
│  4. Select more seats (same issue_id)                       │
│     POST /api/seat-requests  { issue_id: "IE-..." }         │
│                                                             │
│  5. Deselect a seat                                         │
│     POST /api/seat-requests/cancel                          │
│     { seat_inventory_id, issue_id }                         │
│                                                             │
│  6. Fill passenger details                                  │
│     → frontend state only (no API call needed)             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
              User clicks "Confirm Booking"
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  PHASE 2 — LOGIN WALL                                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Login/Register modal appears                               │
│  POST /api/login   or   POST /api/register                  │
│  → returns JWT token                                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  PHASE 3 — AUTHENTICATED CONFIRMATION                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  7. Confirm booking                                         │
│     POST /api/booking/confirm   (requires JWT)              │
│     {                                                       │
│       issue_id: "IE-20250709-123456-ABCDEF",               │
│       passenger_name: "John Doe",                           │
│       passenger_phone: "01700000000",                       │
│       passenger_email: "john@example.com",                  │
│       boarding_id: 1,                                       │
│       dropping_id: 2                                        │
│     }                                                       │
│     → claims all SeatRequests under issue_id               │
│     → sets user_id on those requests                        │
│     → creates Customer if not exists (by phone)            │
│     → creates Booking + BookingDetails                      │
│     → seat status: Blocked → Booked                        │
│     → returns { pnr_number, booking_id, seats, amount }    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Key Design Decision: `issue_id` as Guest Identity

The `issue_id` (e.g. `IE-20250709-123456-ABCDEF`) already exists in `seat_requests`. It is promoted to serve as the **guest session token**.

- Frontend stores `issue_id` in `localStorage` after the first seat hold
- All subsequent seat holds in the same session pass the same `issue_id`
- Cancellations are authorised by `issue_id` alone (not `user_id`) for guests
- At confirmation, the authenticated user's `id` is written to all `SeatRequest` records for that `issue_id`

---

## Implementation Steps

### Step 1 — Database Migration

**File:** new migration `make_user_id_nullable_on_seat_requests_table`

```php
// seat_requests.user_id: NOT NULL → nullable
Schema::table('seat_requests', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id')->nullable()->change();
});
```

> `seat_inventories.last_locked_user_id` is already nullable — no change needed.

---

### Step 2 — Service Layer Changes

**File:** `app/Services/SeatRequestService.php`

#### 2a. Change all method signatures
```php
// Before
public function store(array $attributes, int $userId): array
public function bookOrBlock(array $attributes, int $userId): array
public function cancelBookBlock(array $attributes, int $userId): array
public function cancel(array $attributes, int $userId): array
public function cancelIssue(array $attributes, int $userId): array
private function getIssueSeats(string $issueId, int $userId): array

// After
public function store(array $attributes, ?int $userId): array
public function bookOrBlock(array $attributes, ?int $userId): array
public function cancelBookBlock(array $attributes, int $userId): array  // stays int — only auth users cancel booked seats
public function cancel(array $attributes, string $issueId, ?int $userId): array  // add issueId param for guest cancel
public function cancelIssue(array $attributes, ?int $userId): array
private function getIssueSeats(string $issueId, ?int $userId): array
```

#### 2b. Guest ownership check in `cancel()`
```php
// For guests (userId is null) — verify ownership by issue_id only
// For authenticated users — verify by user_id as before
if ($userId !== null) {
    $query->where('user_id', $userId);
}
$query->where('issue_id', $issueId);
```

#### 2c. Guest `getIssueSeats()` query
```php
$query = SeatRequest::query()->where('issue_id', $issueId);
if ($userId !== null) {
    $query->where('user_id', $userId);
}
```

---

### Step 3 — Public Form Requests

**Directory:** `app/Http/Requests/PublicApi/SeatRequest/`

| File | Rules |
|---|---|
| `SeatRequestStoreRequest.php` | `seat_inventory_id` required integer, `trip_id` required integer, `issue_id` sometimes string max:100, `notes` sometimes string max:500 |
| `SeatRequestCancelRequest.php` | `seat_inventory_id` required integer, `issue_id` required string max:100 |
| `SeatRequestCancelIssueRequest.php` | `issue_id` required string max:100 |

All three: `authorize(): bool { return true; }` — no auth needed.

---

### Step 4 — Public Controller

**File:** `app/Http/Controllers/Api/Public/SeatRequestController.php`

```php
namespace App\Http\Controllers\Api\Public;

class SeatRequestController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SeatRequestService $seatRequestService) {}

    // POST /seat-requests
    public function seatRequest(SeatRequestStoreRequest $request): JsonResponse
    {
        // auth()->id() returns null for guests — that is intentional
        $data = $this->seatRequestService->store($request->validated(), auth()->id());
        return $this->createdResponse(new SeatRequestResource($data), 'Seat blocked successfully for 5 minutes');
    }

    // POST /seat-requests/cancel
    public function removeSeatRequest(SeatRequestCancelRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->seatRequestService->cancel($validated, $validated['issue_id'], auth()->id());
        return $this->successResponse(new SeatRequestResource($data), 'Seat request cancelled successfully');
    }

    // POST /seat-requests/cancel-issue
    public function removeAllSeatsFromIssue(SeatRequestCancelIssueRequest $request): JsonResponse
    {
        $data = $this->seatRequestService->cancelIssue($request->validated(), auth()->id());
        return $this->successResponse(new SeatRequestResource($data), 'All seats cancelled from issue successfully');
    }
}
```

---

### Step 5 — Public Booking Confirmation

**File:** `app/Http/Controllers/Api/Public/BookingConfirmController.php`

**File:** `app/Http/Requests/PublicApi/Booking/BookingConfirmRequest.php`

**File:** `app/Services/PublicBookingService.php` *(new)*

#### Confirm endpoint — `POST /api/booking/confirm` (requires JWT)

**Request payload:**
```json
{
  "issue_id": "IE-20250709-123456-ABCDEF",
  "passenger_name": "John Doe",
  "passenger_phone": "01700000000",
  "passenger_email": "john@example.com",
  "boarding_id": 1,
  "dropping_id": 2
}
```

**`PublicBookingService::confirm()` logic:**
1. Find all `SeatRequest` records where `issue_id = ?` and `status = pending`
2. If none found → abort 404 "No pending seat holds found for this issue"
3. If any `blocked_until < now()` → abort 422 "One or more seats have expired. Please re-select."
4. Find or create `Customer` by `passenger_phone`
5. Claim the seat requests: update `user_id = auth()->id()` on all of them
6. Create `Booking` record (PNR generated)
7. Create `BookingDetail` for each seat
8. Update each `SeatInventory` → `booking_status = STATUS_BOOKED`
9. Update each `SeatRequest` → `status = booked`
10. Return booking with PNR

**Response:**
```json
{
  "status": "success",
  "data": {
    "pnr_number": "PNR-XXXXXXXX",
    "booking_id": 123,
    "issue_id": "IE-20250709-123456-ABCDEF",
    "passenger": { "name": "John Doe", "phone": "01700000000" },
    "trip": { "date": "2025-07-10", "route": "Dhaka → Chittagong" },
    "seats": [ { "seat_number": "A1", "amount": "850.000" } ],
    "total_amount": "850.000"
  }
}
```

---

### Step 6 — Route Changes

**File:** `routes/api.php`

#### Add to public group (no auth):
```php
// Public seat hold
Route::prefix('seat-requests')->group(function () {
    Route::post('/', [App\Http\Controllers\Api\Public\SeatRequestController::class, 'seatRequest']);
    Route::post('/cancel', [App\Http\Controllers\Api\Public\SeatRequestController::class, 'removeSeatRequest']);
    Route::post('/cancel-issue', [App\Http\Controllers\Api\Public\SeatRequestController::class, 'removeAllSeatsFromIssue']);
});
```

#### Add to protected group (JWT required):
```php
// Public booking confirmation (requires login)
Route::post('booking/confirm', [App\Http\Controllers\Api\Public\BookingConfirmController::class, 'confirm']);
```

> The existing `/admin/seat-requests` and `/admin/bookings` routes remain untouched.

---

## Files to Create / Modify

### New files
| File | Type |
|---|---|
| `database/migrations/xxxx_make_user_id_nullable_on_seat_requests_table.php` | Migration |
| `app/Http/Requests/PublicApi/SeatRequest/SeatRequestStoreRequest.php` | Form Request |
| `app/Http/Requests/PublicApi/SeatRequest/SeatRequestCancelRequest.php` | Form Request |
| `app/Http/Requests/PublicApi/SeatRequest/SeatRequestCancelIssueRequest.php` | Form Request |
| `app/Http/Requests/PublicApi/Booking/BookingConfirmRequest.php` | Form Request |
| `app/Http/Controllers/Api/Public/SeatRequestController.php` | Controller |
| `app/Http/Controllers/Api/Public/BookingConfirmController.php` | Controller |
| `app/Services/PublicBookingService.php` | Service |

### Modified files
| File | Change |
|---|---|
| `app/Services/SeatRequestService.php` | `int $userId` → `?int $userId`, guest ownership by `issue_id` |
| `routes/api.php` | Add public seat-request routes + booking/confirm route |

---

## API Endpoint Summary (Public Booking Flow)

| Method | Endpoint | Auth | Purpose |
|---|---|---|---|
| `GET` | `/api/search-trips` | None | Search available trips |
| `GET` | `/api/trip-instances/{id}` | None | View seat plan |
| `POST` | `/api/seat-requests` | None | Hold a seat (5 min) |
| `POST` | `/api/seat-requests/cancel` | None | Release a held seat |
| `POST` | `/api/seat-requests/cancel-issue` | None | Release all seats in session |
| `POST` | `/api/login` | None | Customer login |
| `POST` | `/api/register` | None | Customer register |
| `POST` | `/api/booking/confirm` | JWT | Confirm booking (login wall) |

---

## Frontend Integration Notes

1. After first `POST /seat-requests`, store `issue_id` in `localStorage`
2. Pass the same `issue_id` in all subsequent seat holds for the same booking session
3. Show a countdown timer from `blocked_until` on each selected seat
4. When user clicks "Confirm" → check if JWT token exists in storage
   - If yes → go straight to `POST /api/booking/confirm`
   - If no → show login/register modal, then `POST /api/booking/confirm`
5. On `POST /api/booking/confirm` success → clear `issue_id` from `localStorage`
6. If the timer expires before confirmation → inform user seats are released, redirect to seat selection

---

## Edge Cases to Handle

| Case | Handling |
|---|---|
| Seat hold expires before confirmation | Return 422 with "seats expired" message, user re-selects |
| Same `issue_id` submitted twice | Idempotent — check for existing pending SeatRequests first |
| User logs in on another device | `issue_id` in localStorage still valid — confirmation works normally |
| Two guests try to hold the same seat | DB transaction + `lockForUpdate()` already handles this (existing logic) |
| Guest cancels after JWT token obtained | Public cancel endpoint still works (issue_id is sufficient) |
