@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Search Trips</h1>

        <h3>Request</h3>
        <p>Search for available trips with a <strong>GET</strong> request:</p>
        <pre><code>GET /search-trips</code></pre>

        <h4>Required Query Parameters:</h4>
        <ul>
            <li><strong>trip_date</strong> - Date of the trip (YYYY-MM-DD format)</li>
            <li><strong>route_start_id</strong> - Starting district/location ID</li>
            <li><strong>route_end_id</strong> - Ending district/location ID</li>
        </ul>

        <h4>Optional Query Parameters:</h4>
        <ul>
            <li><strong>coach_no</strong> - Filter by specific coach number</li>
            <li><strong>schedule_id</strong> - Filter by specific schedule ID</li>
            <li><strong>coach_type</strong> - Filter by specific coach type (only accept: 1 for AC, 2 for Non-AC)</li>
            <li><strong>boarding_counter_id</strong> - Filter by specific boarding counter (only accept existing counter ID)</li>
            <li><strong>dropping_counter_id</strong> - Filter by specific dropping counter (only accept existing counter ID)</li>
            <li><strong>page</strong> - Page number for pagination (default: 1)</li>
            <li><strong>per_page</strong> - Items per page (default: 15)</li>
        </ul>

        <h4>Sample Request:</h4>
        <pre><code>GET /search-trips?trip_date=2025-08-18&route_start_id=2&route_end_id=2&coach_no=101&schedule_id=1</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Active trips retrieved successfully",
  "data": {
    "trips": {
      "current_page": 1,
      "data": [
        {
          "trip_id": 3,
          "trip_date": "2025-08-18",
          "trip_date_formatted": "Monday, August 18, 2025",
          "status": 1,
          "status_name": "Active",
          "coach_type": 1,
          "coach_type_name": "AC",
          "current_partition": "trip_instances_202508",
          "coach_id": 2,
          "coach_no": "101",
          "coach_seat_plan_id": 1,
          "coach_status": 1,
          "bus_id": 1,
          "bus_registration_number": "123",
          "bus_manufacturer": "ad",
          "bus_model_year": 2000,
          "schedule_id": 1,
          "schedule_name": "11:00",
          "route_id": 1,
          "start_id": 2,
          "end_id": 2,
          "distance": 1300,
          "duration": "02:45",
          "start_district_name": "Updated District 1",
          "end_district_name": "Updated District 1",
          "route_display": "Updated District 1 → Updated District 1",
          "driver_id": null,
          "driver_name": null,
          "driver_contact": null,
          "driver_license": null,
          "supervisor_id": null,
          "supervisor_name": null,
          "supervisor_contact": null,
          "seat_plan_id": 1,
          "seat_plan_name": "Bus A",
          "seat_plan_floors": null,
          "seat_plan_rows": 6,
          "seat_plan_cols": 4,
          "total_seats": 0,
          "seat_inventory_summary": {
            "total_seats": 0,
            "available_seats": 0,
            "booked_seats": 0,
            "blocked_seats": 0,
            "occupancy_percentage": 0,
            "has_inventory": true
          },
          "is_ac": true,
          "is_active": true,
          "is_migrated": false,
          "created_at": "2025-08-14T10:22:04.000000Z",
          "updated_at": "2025-08-14T10:22:04.000000Z"
        }
      ],
      "first_page_url": "http://127.0.0.1:8000/api/search-trips?page=1",
      "from": 1,
      "last_page": 1,
      "last_page_url": "http://127.0.0.1:8000/api/search-trips?page=1",
      "links": [
        {
          "url": null,
          "label": "&laquo; Previous",
          "active": false
        },
        {
          "url": "http://127.0.0.1:8000/api/search-trips?page=1",
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
      "path": "http://127.0.0.1:8000/api/search-trips",
      "per_page": 15,
      "prev_page_url": null,
      "to": 1,
      "total": 1
    },
    "search_criteria": {
      "trip_date": "2025-08-18",
      "route_start_id": 2,
      "route_end_id": 2,
      "coach_no": null,
      "schedule_id": null
    },
    "total_trips": 1,
    "boarding_counters": [
        {
            "id": 1,
            "type": "1",
            "address": "Dhaka",
            "land_mark": null,
            "location_url": null,
            "phone": null,
            "mobile": null,
            "email": null,
            "primary_contact_no": null,
            "country": null,
            "district_id": 1,
            "booking_allowed_status": "1",
            "booking_allowed_class": "1",
            "no_of_boarding_allowed": null,
            "sms_status": "1",
            "status": "1",
            "created_by": 1,
            "updated_by": null,
            "created_at": "2025-09-03T14:23:13.000000Z",
            "updated_at": null,
            "deleted_at": null
        }
    ],
    "dropping_counters": [
        {
            "id": 2,
            "type": "1",
            "address": "Chittagong",
            "land_mark": null,
            "location_url": null,
            "phone": null,
            "mobile": null,
            "email": null,
            "primary_contact_no": null,
            "country": null,
            "district_id": 2,
            "booking_allowed_status": "1",
            "booking_allowed_class": "1",
            "no_of_boarding_allowed": null,
            "sms_status": "1",
            "status": "1",
            "created_by": 1,
            "updated_by": null,
            "created_at": "2025-09-03T14:23:13.000000Z",
            "updated_at": null,
            "deleted_at": null
        }
    ]
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Structure:</h3>
        <ul>
            <li><strong>trips</strong> - Paginated list of matching trips with complete details</li>
            <li><strong>search_criteria</strong> - Echo of the search parameters used</li>
            <li><strong>total_trips</strong> - Total number of trips found matching the criteria</li>
        </ul>

        <h3>Trip Information Fields:</h3>
        <ul>
            <li><strong>Basic Trip Data</strong> - trip_id, trip_date, status, coach_type</li>
            <li><strong>Coach Information</strong> - coach_id, coach_no, coach_status</li>
            <li><strong>Bus Details</strong> - bus_registration_number, bus_manufacturer, bus_model_year</li>
            <li><strong>Schedule</strong> - schedule_id, schedule_name (departure time)</li>
            <li><strong>Route Details</strong> - start/end districts, distance, duration, route_display</li>
            <li><strong>Staff</strong> - driver and supervisor information (if assigned)</li>
            <li><strong>Seat Plan</strong> - seat_plan_name, rows, cols, total_seats</li>
            <li><strong>Seat Inventory Summary</strong> - available, booked, blocked seats and occupancy percentage</li>
        </ul>

        <h3>Coach Types:</h3>
        <ul>
            <li><strong>1 (AC)</strong> - Air-conditioned coach</li>
            <li><strong>2 (Non-AC)</strong> - Non air-conditioned coach</li>
        </ul>

        <h3>Status Values:</h3>
        <ul>
            <li><strong>1 (Active)</strong> - Trip is active and available for booking</li>
            <li><strong>0 (Inactive)</strong> - Trip is inactive</li>
        </ul>

        <h3>Seat Inventory Summary:</h3>
        <ul>
            <li><strong>total_seats</strong> - Total seats in the coach</li>
            <li><strong>available_seats</strong> - Seats available for booking</li>
            <li><strong>booked_seats</strong> - Seats already booked</li>
            <li><strong>blocked_seats</strong> - Seats temporarily blocked</li>
            <li><strong>occupancy_percentage</strong> - Percentage of seats occupied</li>
            <li><strong>has_inventory</strong> - Whether seat inventory exists for this trip</li>
        </ul>

        <h3>Pagination:</h3>
        <p>The response includes standard Laravel pagination with links, page numbers, and navigation URLs for easy frontend implementation.</p>

        <h3>Partition Information:</h3>
        <p>The <strong>current_partition</strong> field shows which database partition is being used for the trip data, formatted as "trip_instances_YYYYMM".</p>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Trip Booking</strong> - Find available trips for passengers</li>
            <li><strong>Route Planning</strong> - Search trips between specific locations</li>
            <li><strong>Schedule Management</strong> - Filter trips by specific schedules</li>
            <li><strong>Fleet Management</strong> - Search trips by coach number</li>
            <li><strong>Availability Check</strong> - Check seat availability and occupancy</li>
        </ul>

        <h3>Error Responses:</h3>
        <div class="card">
            <div class="card-header">
                <strong>400 Bad Request - Missing Required Parameters</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 400,
  "message": "Required parameters missing",
  "errors": {
    "trip_date": ["Trip date is required"],
    "route_start_id": ["Start location is required"],
    "route_end_id": ["End location is required"]
  }
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>404 Not Found - No Trips Available</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "No trips found matching the criteria",
  "data": {
    "trips": {
      "current_page": 1,
      "data": [],
      "total": 0
    },
    "search_criteria": {
      "trip_date": "2025-08-18",
      "route_start_id": 2,
      "route_end_id": 3
    },
    "total_trips": 0
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
