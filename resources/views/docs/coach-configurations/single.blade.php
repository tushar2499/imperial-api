@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Coach Configuration</h1>

        <h3>Request</h3>
        <p>Retrieve a specific coach configuration by ID:</p>
        <pre><code>GET /coach-configurations/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "success": true,
  "message": "Coach configuration retrieved successfully",
  "data": {
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
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-08-10T10:00:00.000000Z",
        "updated_at": "2025-08-10T10:00:00.000000Z",
        "counter": {
          "id": 1,
          "name": "Kallyanpur Counter",
          "location": "Kallyanpur, Dhaka"
        }
      }
    ]
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Complete Configuration</strong> - Includes all related data (coach, schedule, bus, seat plan, route)</li>
            <li><strong>Boarding/Dropping Points</strong> - Array of all associated boarding and dropping points with counter details</li>
            <li><strong>Timestamps</strong> - Creation and update timestamps for audit trail</li>
            <li><strong>User Tracking</strong> - Shows who created and last updated the configuration</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The coach configuration ID is required in the URL to specify which configuration to retrieve.</li>
            <li>If the coach configuration does not exist or has been deleted, the API will return a 404 error.</li>
            <li>The response includes complete related data from all associated tables.</li>
            <li>Boarding/dropping points are ordered by time for easy visualization of the route.</li>
        </ul>
    </div>
@endsection
