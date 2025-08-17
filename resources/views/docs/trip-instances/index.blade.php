@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Trip Instances</h1>

        <h3>Request</h3>
        <p>Retrieve all trip instances with a <strong>GET</strong> request:</p>
        <pre><code>GET /trip-instances</code></pre>

        <h4>Sample Response:</h4>
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
        "coach_id": 2,
        "bus_id": 1,
        "schedule_id": 1,
        "seat_plan_id": 1,
        "route_id": 1,
        "coach_type": 1,
        "driver_id": null,
        "supervisor_id": null,
        "trip_date": "2025-08-17T00:00:00.000000Z",
        "status": 1,
        "migrated_trip_id": null,
        "created_by": 2,
        "updated_by": null,
        "migrated_by": null,
        "created_at": "2025-08-14T10:21:22.000000Z",
        "updated_at": "2025-08-14T10:21:22.000000Z"
      },
      {
        "id": 3,
        "coach_id": 2,
        "bus_id": 1,
        "schedule_id": 1,
        "seat_plan_id": 1,
        "route_id": 1,
        "coach_type": 1,
        "driver_id": null,
        "supervisor_id": null,
        "trip_date": "2025-08-18T00:00:00.000000Z",
        "status": 1,
        "migrated_trip_id": null,
        "created_by": 2,
        "updated_by": null,
        "migrated_by": null,
        "created_at": "2025-08-14T10:22:04.000000Z",
        "updated_at": "2025-08-14T10:22:04.000000Z"
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

        <h3>Response Structure:</h3>
        <ul>
            <li><strong>Pagination</strong> - Includes pagination metadata (current_page, total, per_page, links)</li>
            <li><strong>Trip Data</strong> - Each trip instance includes all configuration details</li>
            <li><strong>Coach Type</strong> - 1 for AC, 2 for Non-AC</li>
            <li><strong>Status</strong> - 1 for Active, 0 for Inactive</li>
            <li><strong>Migration Support</strong> - Includes migrated_trip_id for data migration tracking</li>
        </ul>

        <h3>Key Fields:</h3>
        <ul>
            <li><strong>trip_date</strong> - The date when the trip is scheduled</li>
            <li><strong>coach_id</strong> - Reference to the coach being used</li>
            <li><strong>bus_id</strong> - Reference to the physical bus</li>
            <li><strong>schedule_id</strong> - Reference to the time schedule</li>
            <li><strong>seat_plan_id</strong> - Reference to the seating arrangement</li>
            <li><strong>route_id</strong> - Reference to the travel route</li>
            <li><strong>driver_id, supervisor_id</strong> - Staff assignments (optional)</li>
        </ul>
    </div>
@endsection
