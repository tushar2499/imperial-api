@extends('layouts.app')
@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Trip Instances</h1>
        <h3>Request</h3>
        <p>Retrieve all trip instances with a <strong>GET</strong> request:</p>
        <pre><code>GET /trip-instances</code></pre>

        <h3>Available Query Parameters:</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>trip_date</code></td>
                        <td>Date</td>
                        <td>Filter by specific trip date (YYYY-MM-DD)</td>
                        <td>2025-08-17</td>
                    </tr>
                    <tr>
                        <td><code>start_date</code></td>
                        <td>Date</td>
                        <td>Start date for date range query</td>
                        <td>2025-08-01</td>
                    </tr>
                    <tr>
                        <td><code>end_date</code></td>
                        <td>Date</td>
                        <td>End date for date range query</td>
                        <td>2025-08-31</td>
                    </tr>
                    <tr>
                        <td><code>status</code></td>
                        <td>Integer</td>
                        <td>Filter by status (0: Inactive, 1: Active, 2: Migrated)</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code>coach_type</code></td>
                        <td>Integer</td>
                        <td>Filter by coach type (1: AC, 2: Non-AC)</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code>coach_id</code></td>
                        <td>Integer</td>
                        <td>Filter by specific coach</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td><code>route_id</code></td>
                        <td>Integer</td>
                        <td>Filter by specific route</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code>today</code></td>
                        <td>Boolean</td>
                        <td>Filter for today's trips only</td>
                        <td>true</td>
                    </tr>
                    <tr>
                        <td><code>upcoming</code></td>
                        <td>Boolean</td>
                        <td>Filter for upcoming trips</td>
                        <td>true</td>
                    </tr>
                    <tr>
                        <td><code>past</code></td>
                        <td>Boolean</td>
                        <td>Filter for past trips</td>
                        <td>true</td>
                    </tr>
                    <tr>
                        <td><code>per_page</code></td>
                        <td>Integer</td>
                        <td>Number of items per page (max: 100)</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td><code>sort_by</code></td>
                        <td>String</td>
                        <td>Sort field (trip_date, created_at, etc.)</td>
                        <td>trip_date</td>
                    </tr>
                    <tr>
                        <td><code>sort_order</code></td>
                        <td>String</td>
                        <td>Sort order (asc, desc)</td>
                        <td>desc</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4>Sample Response (Comprehensive Format):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Trip instances retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 2,
        "trip_id": 2,
        "trip_date": "2025-08-17",
        "trip_date_formatted": "Sunday, August 17, 2025",
        "status": 1,
        "status_name": "Active",
        "coach_type": 1,
        "coach_type_name": "AC",
        "migrated_trip_id": null,
        "coach_id": 2,
        "coach": {
          "id": 2,
          "coach_no": "101",
          "seat_plan_id": 1,
          "coach_type": "1",
          "coach_type_name": "AC",
          "status": 1
        },
        "bus_id": 1,
        "bus": {
          "id": 1,
          "registration_number": "123",
          "manufacturer_company": "ad",
          "model_year": 2000,
          "color": null,
          "status": 1
        },
        "schedule_id": 1,
        "schedule": {
          "id": 1,
          "name": "11:00",
          "status": 0
        },
        "route_id": 1,
        "route": {
          "id": 1,
          "start_id": 2,
          "end_id": 2,
          "distance": 1300,
          "duration": "02:45",
          "status": "1",
          "start_district": {
            "id": 2,
            "name": "Updated District 1",
            "code": "UD1"
          },
          "end_district": {
            "id": 2,
            "name": "Updated District 1",
            "code": "UD1"
          },
          "route_display": "Updated District 1 → Updated District 1"
        },
        "driver_id": null,
        "driver": {
          "id": null,
          "name": null,
          "contact_no": null,
          "email": null,
          "license_no": null,
          "license_expired_date": null,
          "status": null
        },
        "supervisor_id": null,
        "supervisor": {
          "id": null,
          "name": null,
          "contact_no": null,
          "email": null,
          "status": null
        },
        "seat_plan_id": 1,
        "seat_plan": {
          "id": 1,
          "name": "Bus A",
          "floor": null,
          "rows": 6,
          "cols": 4,
          "layout_type": "2-2"
        },
        "total_seats": 24,
        "seat_inventory_summary": {
          "total_seats": 24,
          "available_seats": 24,
          "booked_seats": 0,
          "blocked_seats": 0,
          "occupancy_percentage": 0,
          "has_inventory": true
        },
        "is_ac": true,
        "is_active": true,
        "is_migrated": false,
        "migrated_trip": null,
        "created_by": 2,
        "updated_by": null,
        "migrated_by": null,
        "created_at": "2025-08-14T10:21:22.000000Z",
        "updated_at": "2025-08-14T10:21:22.000000Z"
      }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/trip-instances?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/trip-instances?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/trip-instances?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": null,
    "path": "http://127.0.0.1:8000/api/trip-instances",
    "per_page": 15,
    "prev_page_url": null,
    "to": 2,
    "total": 2
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Structure Overview:</h3>
        <div class="row">
            <div class="col-md-6">
                <h4>Core Trip Information</h4>
                <ul>
                    <li><strong>id / trip_id</strong> - Unique trip identifier</li>
                    <li><strong>trip_date</strong> - Trip date (YYYY-MM-DD)</li>
                    <li><strong>trip_date_formatted</strong> - Human-readable date</li>
                    <li><strong>status</strong> - Numeric status (0/1/2)</li>
                    <li><strong>status_name</strong> - Status description</li>
                    <li><strong>coach_type</strong> - Coach type (1: AC, 2: Non-AC)</li>
                    <li><strong>coach_type_name</strong> - Coach type description</li>
                </ul>

                <h4>Related Entities</h4>
                <ul>
                    <li><strong>coach</strong> - Coach details with number and type</li>
                    <li><strong>bus</strong> - Bus registration and specifications</li>
                    <li><strong>schedule</strong> - Time schedule information</li>
                    <li><strong>route</strong> - Route with start/end districts</li>
                    <li><strong>seat_plan</strong> - Seating arrangement details</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h4>Staff Information</h4>
                <ul>
                    <li><strong>driver</strong> - Driver details and license info</li>
                    <li><strong>supervisor</strong> - Supervisor contact information</li>
                </ul>

                <h4>Seat Management</h4>
                <ul>
                    <li><strong>total_seats</strong> - Total available seats</li>
                    <li><strong>seat_inventory_summary</strong> - Real-time seat status:
                        <ul>
                            <li>available_seats</li>
                            <li>booked_seats</li>
                            <li>blocked_seats</li>
                            <li>occupancy_percentage</li>
                        </ul>
                    </li>
                </ul>

                <h4>Status Flags</h4>
                <ul>
                    <li><strong>is_ac</strong> - Boolean: AC coach</li>
                    <li><strong>is_active</strong> - Boolean: Active trip</li>
                    <li><strong>is_migrated</strong> - Boolean: Migrated trip</li>
                </ul>

                <h4>Route Information</h4>
                <ul>
                    <li><strong>start_district</strong> - Origin district details</li>
                    <li><strong>end_district</strong> - Destination district details</li>
                    <li><strong>route_display</strong> - Formatted route string</li>
                    <li><strong>distance</strong> - Route distance</li>
                    <li><strong>duration</strong> - Estimated travel time</li>
                </ul>
            </div>
        </div>

        <h3>Status Values:</h3>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>0</code></td>
                        <td>Inactive</td>
                        <td>Trip is disabled/cancelled</td>
                    </tr>
                    <tr>
                        <td><code>1</code></td>
                        <td>Active</td>
                        <td>Trip is active and bookable</td>
                    </tr>
                    <tr>
                        <td><code>2</code></td>
                        <td>Migrated</td>
                        <td>Trip has been migrated to another trip</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3>Coach Types:</h3>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Value</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>1</code></td>
                        <td>AC</td>
                        <td>Air-conditioned coach</td>
                    </tr>
                    <tr>
                        <td><code>2</code></td>
                        <td>Non-AC</td>
                        <td>Non air-conditioned coach</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3>Seat Inventory Status:</h3>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>1</code></td>
                        <td>Available</td>
                        <td>Seat is available for booking</td>
                    </tr>
                    <tr>
                        <td><code>2</code></td>
                        <td>Booked</td>
                        <td>Seat is confirmed and booked</td>
                    </tr>
                    <tr>
                        <td><code>3</code></td>
                        <td>Blocked</td>
                        <td>Seat is temporarily blocked</td>
                    </tr>
                    <tr>
                        <td><code>0</code></td>
                        <td>Cancelled</td>
                        <td>Seat booking was cancelled</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3>Key Features:</h3>
        <div class="row">
            <div class="col-md-6">
                <h4>Pagination Support</h4>
                <ul>
                    <li>Standard Laravel pagination</li>
                    <li>Configurable per_page (max 100)</li>
                    <li>Full pagination metadata included</li>
                </ul>

                <h4>Date Range Queries</h4>
                <ul>
                    <li>Single date filtering</li>
                    <li>Date range queries across partitions</li>
                    <li>Smart partition management</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h4>Real-time Data</h4>
                <ul>
                    <li>Live seat availability</li>
                    <li>Occupancy percentages</li>
                    <li>Current booking status</li>
                </ul>

                <h4>Comprehensive Relations</h4>
                <ul>
                    <li>All related entities included</li>
                    <li>District information with codes</li>
                    <li>Staff contact details</li>
                </ul>
            </div>
        </div>

        <h3>Example API Calls:</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
# Get all trips
GET /api/trip-instances

# Get trips for specific date
GET /api/trip-instances?trip_date=2025-08-17

# Get active AC trips
GET /api/trip-instances?status=1&coach_type=1

# Get trips for date range
GET /api/trip-instances?start_date=2025-08-01&end_date=2025-08-31

# Get today's trips only
GET /api/trip-instances?today=true

# Get upcoming trips with pagination
GET /api/trip-instances?upcoming=true&per_page=20&page=2
                </code></pre>
            </div>
        </div>
    </div>
@endsection
