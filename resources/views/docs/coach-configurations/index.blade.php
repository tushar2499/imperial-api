@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Coach Configurations</h1>

        <h3>Request</h3>
        <p>Retrieve all coach configurations with a <strong>GET</strong> request:</p>
        <pre><code>GET /coach-configurations</code></pre>

        <h4>Query Parameters (Optional):</h4>
        <ul>
            <li><strong>status</strong> - Filter by status (0 or 1)</li>
            <li><strong>coach_type</strong> - Filter by coach type (1 for AC, 2 for Non-AC)</li>
            <li><strong>coach_id</strong> - Filter by coach ID</li>
            <li><strong>schedule_id</strong> - Filter by schedule ID</li>
            <li><strong>route_id</strong> - Filter by route ID</li>
            <li><strong>per_page</strong> - Items per page (default: 15)</li>
            <li><strong>page</strong> - Page number</li>
        </ul>

        <h4>Sample Request with Parameters:</h4>
        <pre><code>GET /coach-configurations?status=1&coach_type=1&per_page=10&page=1</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "success": true,
  "message": "Coach configurations retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "coach_id": 1,
        "schedule_id": 2,
        "bus_id": 3,
        "seat_plan_id": 4,
        "route_id": 5,
        "coach_type": 1,
        "status": 1,
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-08-10T10:00:00.000000Z",
        "updated_at": "2025-08-10T10:00:00.000000Z",
        "coach": {
          "id": 1,
          "name": "Express Coach 001",
          "code": "EC001"
        },
        "schedule": {
          "id": 2,
          "name": "Morning Schedule",
          "departure_time": "08:00:00"
        },
        "bus": {
          "id": 3,
          "registration_number": "DH-123456",
          "model": "Volvo B11R"
        },
        "seat_plan": {
          "id": 4,
          "name": "2x2 Layout",
          "total_seats": 40
        },
        "route": {
          "id": 5,
          "name": "Dhaka - Chittagong",
          "distance": "264 km"
        },
        "boarding_droppings": [
          {
            "id": 1,
            "coach_configuration_id": 1,
            "counter_id": 1,
            "type": 1,
            "time": "08:00:00",
            "starting_point_status": 1,
            "ending_point_status": 0,
            "status": 1,
            "counter": {
              "id": 1,
              "name": "Kallyanpur Counter",
              "location": "Kallyanpur, Dhaka"
            }
          }
        ]
      }
    ],
    "first_page_url": "http://localhost/api/coach-configurations?page=1",
    "from": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Structure:</h3>
        <ul>
            <li><strong>Pagination</strong> - Includes pagination metadata (current_page, total, per_page, etc.)</li>
            <li><strong>Related Data</strong> - Each configuration includes coach, schedule, bus, seat_plan, route, and boarding_droppings</li>
            <li><strong>Coach Type</strong> - 1 for AC, 2 for Non-AC</li>
            <li><strong>Boarding/Dropping Points</strong> - Type 1 = Boarding, Type 2 = Dropping</li>
        </ul>
    </div>
@endsection
