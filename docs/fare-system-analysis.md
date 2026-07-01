# Imperial Bus Ticketing — Fare System Analysis & Redesign Proposal

## 1. Current System — What You Have

### Domain Map (as understood from code review)
```
District ← Station ← TransportRoute (start_id, end_id)
                            ↓
                     CoachConfiguration (coach + schedule + route + boarding/dropping stops)
                            ↓
                     TripInstance (one real trip on a date, partitioned)
                            ↓
                     SeatInventory (per-seat, partitioned)
                            ↓
                     SeatRequest (5-min hold) → Booking + BookingDetail
```

**Real-world example flow:**
```
District: Dhaka, Chittagong
TransportRoute: Dhaka → Chittagong (route_id=5)
Station: Comilla (intermediate stop on route 5)

CoachConfiguration:
  coach_id=3 (Green AC Sleeper), schedule_id=7 (8:00 PM departure),
  transport_route_id=5, coach_type=AC (1)
  Boarding: Kalyanpur (counter_id=2), Shyamoli (counter_id=4)
  Dropping:  Chittagong Dampara (counter_id=10)

TripInstance (2026-07-01):
  → stored in table trip_instances_202607
  → coach_id=3, route_id=5, trip_date=2026-07-01

SeatInventory (for each seat in the coach):
  seat_id=101, booking_status=Available
  seat_id=102, booking_status=Booked

Booking: PNR=IMP-20260701-001, customer boards Kalyanpur, drops Dampara
```

### Current `fares` Table Schema
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| route_id | bigint | FK → transport_routes |
| seat_plan_id | bigint | FK → seat_plans |
| coach_type | tinyint | 1=AC, 2=Non-AC |
| seat_type | enum | Suite Class, Business Class, Sleeper, Economy |
| from_date | datetime nullable | Validity window start |
| to_date | datetime nullable | Validity window end |
| status | tinyint | 1=Active, 0=Inactive |
| created_by / updated_by | bigint | |
| timestamps + softDeletes | | |

> **Critical Gap:** The `fares` table has NO `amount` (price) column! The `TripInstance` model calls `$fare->amount` in `getFareAmountForSeatType()` and `getFareAmount()`, but this column does not exist in the migration. The fare system is structurally incomplete.

**Example of a current fare record (what is actually stored):**
```
id=1, route_id=5, seat_plan_id=2, coach_type=1 (AC),
seat_type='Economy', from_date=NULL, to_date=NULL, status=1
← NO amount column → $fare->amount returns NULL everywhere!
```

### Current Fare Lookup Logic (TripInstance)
```php
Fare::where('route_id', $routeId)
    ->where('seat_plan_id', $seatPlanId)
    ->where('coach_type', $coachType)
    ->where('seat_type', $seatType)
    ->where('status', 1)
    ->first();
```

**Example lookup for a booking:**
```
Passenger books seat on: route_id=5, seat_plan_id=2, coach_type=1 (AC), seat_type='Economy'
Query finds: Fare id=1 ✅
Then calls: $fare->amount  → returns NULL ❌ (column doesn't exist)
Result: Booking is created with price = NULL — completely broken!
```

**The lookup key is:** `route + seat_plan + coach_type + seat_type`

---

## 2. How Industry Leaders Handle Fares

### RedBus (India's largest bus booking platform)
**Fare Model:** Operator-defined, seat-class based
- Each **bus operator** sets their own fare per route
- Fares are attached to **seat types** within a bus (sleeper, semi-sleeper, seater)
- Fare = **base fare** + **operator service fee** + **platform convenience fee**
- Support for **dynamic pricing** (price changes based on demand/seats remaining)
- **Partial route pricing**: Different fares for boarding from intermediate stops (not just origin → destination)
- Promotional fares via coupon codes applied at checkout

**Key insight:** RedBus fare key = `operator + route + coach_type + seat_class + boarding_point + dropping_point`

**Example:**
```
Operator : Greenline Travels
Route    : Dhaka → Chittagong
Coach    : AC Sleeper
Boarding : Dhaka (Kalyanpur Counter)   → Fare: ৳ 1,200
Boarding : Dhaka (Shyamoli Counter)    → Fare: ৳ 1,150  ← different boarding, lower price
Dropping : Chittagong (Dam Pukur)      → Fare: ৳ 1,200
Dropping : Chittagong (Oxygen More)    → Fare: ৳ 1,100  ← drops before endpoint, cheaper

So the same Sleeper seat on the same bus costs different amounts
depending on WHERE you board and WHERE you get off.
```

---

### BDTicket (Bangladesh)
**Fare Model:** Fixed fares per operator route, set by operators
- Fares defined per **company + route + coach_type**
- Coach class determines fare (AC Chair, Non-AC Chair, Sleeper, Business)
- **Same fare for all seats of the same class** (no per-seat pricing)
- Discounts are applied by operators as fixed amounts or percentages
- No dynamic pricing — fares are stable

**Key insight:** BDTicket fare key = `company + route + coach_class`

**Example:**
```
Company : Shyamoli Paribahan
Route   : Dhaka → Rajshahi

  AC Chair Coach   → ৳ 700  (all AC chair seats same price)
  Non-AC Chair     → ৳ 450  (all non-AC seats same price)

Every passenger on AC Chair pays exactly ৳ 700 regardless of
which seat number, which counter they booked from, or season.
Price is fixed and operator-controlled.
```

---

### Shohoz (Bangladesh)
**Fare Model:** Similar to BDTicket but with slight nuances
- Fares are per **route + operator + seat_class**
- Has concept of "seat category" fares within same bus (upper deck vs lower deck in double-decker)
- Support for **child discounts** (typically 50% of adult fare)
- Support for **advance booking discounts**
- **VAT/Tax** is applied separately on top of base fare
- Boarding-point based pricing where different boarding stops in same city can have slightly different fares

**Key insight:** Shohoz fare key = `route + operator + seat_category`, with discount overlays and tax on top

**Example:**
```
Route    : Dhaka → Cox's Bazar
Operator : SR Travels (Double-Decker)

  Lower Deck (Economy)    → base: ৳ 900
  Upper Deck (Business)   → base: ৳ 1,100  ← upper floor costs more

  Child discount: 50% off → Upper: ৳ 550, Lower: ৳ 450
  Advance 7-day discount: 10% off → Upper: ৳ 990
  VAT/Service fee: ৳ 30 flat added by platform

Final price a customer sees:
  Adult, Upper Deck, booked 3 days ahead = ৳ 1,100 + ৳ 30 = ৳ 1,130
  Child, Upper Deck, booked 10 days ahead = (৳ 1,100 × 50%) × 90% + ৳ 30 = ৳ 525 + ৳ 30 = ৳ 555
```

---

## 3. Critical Problems With Your Current Fare System

### Problem 1: No Price Column
The `fares` table has no `amount` column. The `TripInstance::getFareAmount()` method references `$fare->amount` but it doesn't exist.

**Example of the bug:**
```php
$trip->getFareAmountForSeatType('Economy');
// → finds Fare record id=1
// → returns $fare->amount → NULL  ← column does not exist in DB
// → BookingDetail::price stored as NULL
// → Customer invoice shows: Price: 0.00 ৳  ❌
```

### Problem 2: Fare Granularity is Wrong
**Current system attaches fare to:** `route + seat_plan + coach_type + seat_type`

**Problem:** Your `Seat` model has a `seat_type` column on individual seats. But `Fare` uses `seat_type` as a string enum (`Suite Class`, `Business Class`, etc.) which doesn't match seat-level types.

**Example of the mismatch:**
```
Seat Plan: "Green AC Double-Decker"
  seats table: seat_type = 'sleeper'   ← lowercase, informal
  fares table: seat_type = 'Sleeper'   ← TitleCase enum

Fare::where('seat_type', $seat->seat_type) → 'sleeper'
→ No match found because DB has 'Sleeper'
→ Returns NULL → booking has no fare ❌

Also: SeatPlan 2 has 40 Economy seats + 5 Business seats
But fares has ONE record for seat_plan_id=2 → which seats does it apply to?
```

### Problem 3: No Boarding/Dropping Point Pricing
Your `CoachBoardingDropping` has multiple stops per route. All stops currently share the same fare. On routes like Dhaka→Cox's Bazar where passengers board from Chittagong (midway), the fare should differ.

**Example of what's missing:**
```
Route: Dhaka → Cox's Bazar
Boarding stops: Dhaka Kalyanpur, Comilla, Chittagong

Expected fares:
  Board Dhaka    → Cox's Bazar = ৳ 1,400  (full route)
  Board Comilla  → Cox's Bazar = ৳ 1,000  (shorter)
  Board Chitt.   → Cox's Bazar = ৳ 600    (very short)

Current system: ALL three pay ৳ 1,400 — wrong! ❌
```

### Problem 4: No Discount/Promo System at Fare Level
Booking has `total_discount` but there's no system to define *how* discounts are calculated. No discount rules tied to fares.

**Example of the gap:**
```
Admin wants: 10% off for Eid season (June 5–10)
Admin wants: ৳ 50 flat off for return passengers

Current system: booking.total_discount exists, but nothing
defines WHAT the discount is or WHEN it applies.
Cashier must manually type the discount amount each time — error-prone ❌
```

### Problem 5: Fare Has No Amount — Booking Prices Are Opaque
`BookingDetail.price` stores a price but there's no FK linking it back to which `Fare` record was used. There's no audit trail from price → fare rule.

**Example of the audit problem:**
```
BookingDetail: seat_id=101, price=1200.00

Question: Why was ৳ 1,200 charged?
→ No fare_id FK → cannot trace which fare rule set this price
→ If fare amount is later changed to ৳ 1,400, old bookings
   show ৳ 1,200 with no explanation — accountants are confused ❌
```

### Problem 6: VAT/Tax Not Modeled
No tax or service charge columns on fares.

**Example:**
```
Base fare: ৳ 1,000
Platform service fee: ৳ 30
Total shown to customer: ৳ 1,030

Current system: No place to store the ৳ 30 service fee rule.
Cashier must manually add it — inconsistent across counters ❌
```

### Problem 7: Double-Floor (Double-Decker) Bus Pricing
Both floors share the same `seat_plan_id` and `seat_type`, making it impossible to set different fares for upper vs lower deck.

**Example:**
```
Bus: Green Line Double-Decker, Route: Dhaka → Cox's Bazar
SeatPlan id=3 (floor=2)
  Floor 1 (Lower Deck): seats 1A–10D  → should cost ৳ 900
  Floor 2 (Upper Deck): seats 11A–20D → should cost ৳ 1,100

Current fare lookup:
  route_id=7, seat_plan_id=3, coach_type=1, seat_type='Economy'
  → Returns ONE fare record → both floors get same price ৳ 900 ❌
  → Upper deck passengers underpaying by ৳ 200 per ticket!
```

---

## 4. Proposed Fare System Design

### Design Philosophy (matching your system + industry best practices)

The fare must answer: **"How much does passenger pay for seat X on trip Y, boarding at B, dropping at D?"**

The fare key should be:
```
route_id + coach_type + seat_type + floor_number + (optional: boarding_point_id + dropping_point_id)
```

You don't need `seat_plan_id` in the fare lookup — the seat plan is tied to the coach configuration, not the pricing.

**Example — same trip, different passengers, different fares:**
```
Trip: Dhaka → Cox's Bazar, AC Double-Decker, 2026-07-01

Passenger A: boards Dhaka, drops Cox's Bazar, Upper Deck (Economy)
  → route=7, coach_type=1, seat_type=Economy, floor=2, boarding=Dhaka, dropping=CoxsBazar
  → Fare: ৳ 1,100

Passenger B: boards Comilla, drops Cox's Bazar, Lower Deck (Economy)
  → route=7, coach_type=1, seat_type=Economy, floor=1, boarding=Comilla, dropping=CoxsBazar
  → Fare: ৳ 700

Passenger C: boards Dhaka, drops Cox's Bazar, Upper Deck, Eid promo (10% off)
  → Same as A but discount applied → ৳ 1,100 × 90% = ৳ 990
```

---

### Recommended Schema

#### Option A — Minimal Fix (Keep current table, add missing columns)
Add `amount`, `floor_number`, and optionally `vat_percentage` + discount columns to the existing `fares` table.

```sql
ALTER TABLE fares
  ADD COLUMN amount DECIMAL(10,2) NOT NULL AFTER seat_type,
  ADD COLUMN floor_number TINYINT NULL AFTER amount COMMENT '1=Lower, 2=Upper, NULL=all floors',
  ADD COLUMN vat_percentage DECIMAL(5,2) DEFAULT 0 AFTER floor_number,
  ADD COLUMN discount_type ENUM('flat','percentage') NULL AFTER vat_percentage,
  ADD COLUMN discount_value DECIMAL(10,2) NULL AFTER discount_type;
```

✅ Least disruption, works with current code
❌ Still missing boarding/dropping-point pricing
❌ Still missing the fare→booking audit link

---

#### Option B — Proper Industry-Grade Design (Recommended)

Extend the existing `fares` table with all required columns:

```
fares (extended)
├── id
├── route_id (FK → transport_routes)
├── coach_type (1=AC, 2=Non-AC)
├── seat_type (varchar, nullable = all types)
├── floor_number (tinyint, nullable = all floors, 1=Lower, 2=Upper)
├── boarding_point_id (FK → counters, nullable = all boardings)
├── dropping_point_id (FK → counters, nullable = all droppings)
├── amount (DECIMAL 10,2) ← THE ACTUAL PRICE
├── vat_percentage (DECIMAL 5,2, default 0)
├── discount_type (enum: flat, percentage, null)
├── discount_value (DECIMAL 10,2, null)
├── effective_from (date, nullable)
├── effective_to (date, nullable)
├── priority (smallint, default 0) ← higher = checked first for specific overrides
├── status (tinyint, 1=active)
├── created_by / updated_by
├── timestamps + softDeletes
```

**Modified `booking_details`** — add fare audit:
```
booking_details
├── ... (existing)
├── fare_id (FK → fares, nullable) ← which fare rule was used
├── seat_type (varchar) ← snapshot of seat type at booking time
├── floor_number (tinyint) ← snapshot of floor at booking time
```

---

### Fare Resolution Algorithm
When calculating fare for a seat on a trip:

```
1. Get trip details: route_id, coach_type
2. Get seat details: seat.seat_type, seat.seatPlanFloor.floor_number
3. Look up fares (most specific → least specific):
   a. route + coach_type + seat_type + floor_number + boarding + dropping
   b. route + coach_type + seat_type + floor_number + boarding
   c. route + coach_type + seat_type + floor_number
   d. route + coach_type + seat_type
   e. route + coach_type
4. Apply validity: effective_from <= trip_date <= effective_to
5. Apply VAT: amount_with_vat = amount * (1 + vat_pct/100)
6. Apply discount if any
7. Return final_amount
```

**Walkthrough example — Passenger books Upper Deck seat, boards Comilla:**
```
Inputs:
  route_id=7, coach_type=1 (AC), seat_type='Economy',
  floor_number=2 (Upper), boarding_point_id=15 (Comilla), dropping_point_id=22 (Cox's Bazar)

Step 1: Try most specific — route=7 + AC + Economy + floor=2 + boarding=Comilla + dropping=CoxsBazar
        → Found! Fare id=12, amount=৳ 700  ✅ use this

(If not found, fallback chain continues...)
  Try: route=7 + AC + Economy + floor=2 + boarding=Comilla
  Try: route=7 + AC + Economy + floor=2
  Try: route=7 + AC + Economy
  Try: route=7 + AC

Step 2: Check effective_from=NULL, effective_to=NULL → always valid ✅
Step 3: vat_percentage=0 → amount stays ৳ 700
Step 4: discount_type=NULL → no discount
Step 5: Final fare = ৳ 700
```

---

## 5. Double-Floor (Double-Decker) Bus — Fare Problem Detail

### How Your Floor System Works Today

```
SeatPlan
  └── seat_plan_floors (1 or 2 rows)
        ├── Floor 1: name='Lower Deck', rows=10, cols=4, ...
        └── Floor 2: name='Upper Deck', rows=10, cols=4, ...
              └── seats (each seat has seat_plan_floor_id)
```

Every `Seat` already has a `seat_plan_floor_id` linking it back to its floor.
The `SeatPlan.floor` column stores the number of floors (1 or 2).

### The Problem

Your current `fares` table key is: `route + seat_plan + coach_type + seat_type`

But **both floors share the same `seat_plan`** and can share the same `seat_type` (e.g., both floors are "Economy"). There is no floor dimension in the fare lookup, so:
- Lower deck seat → looks up fare → gets 800 BDT
- Upper deck seat → looks up **same fare** → also gets 800 BDT ❌

### How Shohoz / Double-Decker Operators Handle It

In Bangladesh double-decker buses (e.g., Green Line, Shyamoli):
- **Lower deck** is typically cheaper (more legroom but ground level)
- **Upper deck** is typically priced higher (scenic view, preferred)

Shohoz models this as a **seat class** distinction. The upper vs lower deck effectively becomes its own "seat class" in their system.

### Three Possible Solutions

#### Solution A — Use `seat_type` on Individual Seats (Simplest)

Your `Seat` model already has a `seat_type` column. On a double-decker, assign different `seat_type` values to each floor:
- Lower deck seats → `seat_type = 'Economy'`
- Upper deck seats → `seat_type = 'Business Class'`

Then create two fare records for the same route:
```
Fare 1: route=X, coach_type=AC, seat_type=Economy   → amount=800
Fare 2: route=X, coach_type=AC, seat_type=Business  → amount=1000
```

✅ Zero schema changes needed
✅ Works with the existing lookup: `seat.seat_type → fare.seat_type`
❌ Semantic mismatch — floor ≠ seat class conceptually
❌ Operators may use seat_type for real class distinctions AND floor simultaneously

---

#### Solution B — Add `floor_number` to the Fare Table (Recommended ✅)

Add a nullable `floor_number` column to `fares` and `seat_plan_floors`:

```sql
-- Step 1: Label floors in seat_plan_floors
ALTER TABLE seat_plan_floors
  ADD COLUMN floor_number TINYINT NOT NULL DEFAULT 1
  COMMENT '1=Lower/First, 2=Upper/Second';

-- Step 2: Add floor dimension to fares
ALTER TABLE fares
  ADD COLUMN floor_number TINYINT NULL AFTER seat_type
  COMMENT '1=Lower, 2=Upper, NULL=applies to all floors';
```

Fare resolution priority:
```
1. Match route + coach_type + seat_type + floor_number (exact floor match)
2. Match route + coach_type + seat_type + floor_number IS NULL (floor-agnostic fallback)
```

During booking, lookup:
```php
$floorNumber = $seat->seatPlanFloor->floor_number;
Fare::where('route_id', $routeId)
    ->where('coach_type', $coachType)
    ->where('seat_type', $seatType)
    ->where(fn($q) => $q->where('floor_number', $floorNumber)->orWhereNull('floor_number'))
    ->orderByRaw('floor_number IS NULL ASC')  // specific match first
    ->first();
```

✅ Semantically correct — floor is a separate dimension from seat class
✅ Backward compatible — existing single-floor fares use `floor_number = NULL`
✅ No changes to Seat or SeatPlan models
⚠️ Requires `floor_number` on `seat_plan_floors` (floor ordering: 1, 2)

---

#### Solution C — `floor_id` FK in Fare Table (Most Flexible)

Add a nullable `floor_id` FK directly pointing to `seat_plan_floors`:

✅ Most granular control
✅ Admin UI can show "Floor: Upper Deck / Lower Deck" by name
❌ More complex lookup query
❌ Floor IDs change across seat plan versions — brittle

---

## 6. Comparison Table: Current vs Proposed vs Industry

| Feature | Current | Proposed | RedBus/Shohoz |
|---------|---------|----------|---------------|
| Has price column | ❌ | ✅ | ✅ |
| Seat-type based pricing | Partial | ✅ | ✅ |
| Floor-based pricing (double-decker) | ❌ | ✅ | ✅ |
| Date-range validity | ✅ | ✅ | ✅ |
| Boarding-point pricing | ❌ | ✅ | ✅ |
| VAT/Tax support | ❌ | ✅ | ✅ |
| Discount system | ❌ | ✅ | ✅ |
| Fare audit in booking | ❌ | ✅ | ✅ |
| Priority/override system | ❌ | ✅ | ✅ |

---

## 7. Complete Recommended `fares` Table (All Problems Combined)

```sql
-- Alter fares table to add all missing columns
ALTER TABLE fares
  DROP COLUMN seat_plan_id,                    -- not needed in fare lookup
  ADD COLUMN amount DECIMAL(10,2) NOT NULL AFTER seat_type,
  ADD COLUMN floor_number TINYINT NULL AFTER amount
      COMMENT '1=Lower, 2=Upper, NULL=all floors',
  ADD COLUMN boarding_point_id BIGINT NULL AFTER floor_number,
  ADD COLUMN dropping_point_id BIGINT NULL AFTER boarding_point_id,
  ADD COLUMN vat_percentage DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER dropping_point_id,
  ADD COLUMN discount_type ENUM('flat','percentage') NULL AFTER vat_percentage,
  ADD COLUMN discount_value DECIMAL(10,2) NULL AFTER discount_type,
  ADD COLUMN priority SMALLINT NOT NULL DEFAULT 0 AFTER discount_value,
  CHANGE COLUMN from_date effective_from DATE NULL,
  CHANGE COLUMN to_date effective_to DATE NULL;
```

### Fare Resolution Priority Chain
```
Most specific → Least specific
1. route + coach_type + seat_type + floor_number + boarding + dropping
2. route + coach_type + seat_type + floor_number + boarding
3. route + coach_type + seat_type + floor_number
4. route + coach_type + seat_type
5. route + coach_type
```

**Example — what fare records you'd create for a double-decker route:**
```
Route: Dhaka → Cox's Bazar (route_id=7), AC (coach_type=1)

-- Priority 1 (most specific): boarding-point specific, floor-specific
Fare A: route=7, AC, Economy, floor=2(Upper), boarding=Dhaka,    amount=৳ 1,100
Fare B: route=7, AC, Economy, floor=2(Upper), boarding=Comilla,  amount=৳ 700
Fare C: route=7, AC, Economy, floor=1(Lower), boarding=Dhaka,    amount=৳ 900
Fare D: route=7, AC, Economy, floor=1(Lower), boarding=Comilla,  amount=৳ 600

-- Priority 4 (fallback): if no boarding match, use floor-only fare
Fare E: route=7, AC, Economy, floor=2(Upper), boarding=NULL, amount=৳ 1,100
Fare F: route=7, AC, Economy, floor=1(Lower), boarding=NULL, amount=৳ 900

-- Priority 5 (last resort): if no floor/type match at all
Fare G: route=7, AC, seat_type=NULL, floor=NULL, boarding=NULL,  amount=৳ 950

Passenger boards Dhaka, Upper Deck → matches Fare A → ৳ 1,100
Passenger boards Comilla, Lower Deck → matches Fare D → ৳ 600
Passenger boards Chittagong (no specific fare) → falls back to Fare F → ৳ 900
```

---

## 8. Implementation Phases

### Phase 1 — Emergency Fix (immediate, no breaking changes)
- Add `amount` DECIMAL column to `fares` table
- Add `floor_number` to `seat_plan_floors` and `fares`
- Update `FareResource` to expose new fields
- Update `FareStoreRequest` / `FareUpdateRequest` to validate amount + floor_number
- Update `FareService` store/update to handle new fields
- Fix `TripInstance` fare lookup to include floor_number

**After Phase 1 — admin can create:**
```
POST /api/fares
{ route_id: 7, coach_type: 1, seat_type: "Economy",
  floor_number: 2, amount: 1100, status: 1 }
→ Upper Deck Economy fare ৳ 1,100 saved ✅
→ Booking price = ৳ 1,100 (no longer NULL) ✅
```

### Phase 2 — Boarding/Dropping Point Pricing
- Add `boarding_point_id` / `dropping_point_id` (nullable) to `fares`
- Update fare resolution logic in `FareService`
- Integrate into booking flow

**After Phase 2 — admin can create:**
```
POST /api/fares
{ route_id: 7, coach_type: 1, seat_type: "Economy", floor_number: 2,
  boarding_point_id: 15, amount: 700, status: 1 }
→ Passengers boarding from Comilla (counter 15) pay ৳ 700 instead of ৳ 1,100 ✅
```

### Phase 3 — Tax, Discount, Priority
- Add `vat_percentage`, `discount_type`, `discount_value`, `priority` columns
- Build `FareCalculatorService` with the full resolution algorithm
- Integrate into `BookingController` price calculation

**After Phase 3 — fare can include:**
```
{ amount: 1100, vat_percentage: 0, discount_type: "percentage",
  discount_value: 10, effective_from: "2026-06-05", effective_to: "2026-06-10" }
→ Eid promo: ৳ 1,100 × 90% = ৳ 990 auto-applied during Jun 5–10 ✅
```

### Phase 4 — Audit Trail
- Add `fare_id` FK + `floor_number` snapshot to `booking_details`
- Snapshot fare at time of booking to prevent retroactive price changes affecting old bookings

**After Phase 4 — booking_details stores:**
```
booking_details row:
  seat_id=101, fare_id=12, seat_type='Economy', floor_number=2,
  price=700.00, discount=0, amount=700.00
→ 6 months later: admin raises fare to ৳ 800
→ Old booking still shows ৳ 700 with full trace back to fare_id=12 ✅
```

---

## 9. Open Questions (To Confirm Before Implementation)

1. **Do seats in your seat plan have different `seat_type` values?** (e.g., some seats are "Business Class" and others are "Economy" in the same bus?) Or is the entire bus one seat type?

2. **Do you need different fares for different boarding points?** (e.g., Dhaka boarders pay 800 BDT, Gazipur boarders pay 700 BDT for the same Dhaka→Cox's Bazar route?)

3. **Do you need VAT/tax?** Bangladesh transport has no VAT on bus tickets typically, but do you need a service fee?

4. **What discount types do you need?** Flat amount? Percentage? Promo codes? Member discounts?

5. **Is the `seat_type` in `Seat` model the same classification as `seat_type` in `Fare`?** (Both use the same string values?)

6. **For double-decker, which floor costs more — upper or lower?**

7. **Are you ready to drop `seat_plan_id` from `fares`?** It's not needed for fare lookup (the seat plan is already tied to the coach configuration, not the price).
