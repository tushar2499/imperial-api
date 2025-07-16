@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Fare</h1>

        <h3>Request</h3>
        <p>Retrieve a specific fare by ID:</p>
        <pre><code>GET /fares/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Fare retrieved successfully",
  "data": {
    "id": 1,
    "route_id": 1,
    "start_id": 1,
    "end_id": 2,
    "start_name": "Dhaka",
    "end_name": "Chittagong",
    "distance": 244,
    "duration": "5:30",
    "route_status": 1,
    "seat_plan_id": 1,
    "seat_plan_name": "AC Business",
    "coach_type": 1,
    "from_date": "2024-01-01",
    "to_date": "2024-12-31",
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2024-01-01T12:00:00",
    "updated_at": "2024-01-01T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Route Information</strong> - Includes start/end districts, distance, and duration</li>
            <li><strong>Seat Plan</strong> - Shows the associated seat plan name and ID</li>
            <li><strong>Coach Type</strong> - 1 for AC, 2 for Non-AC</li>
            <li><strong>Validity Period</strong> - from_date and to_date show when the fare is valid</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The fare ID is required in the URL to specify which fare to retrieve.</li>
            <li>If the fare does not exist or has been deleted, the API will return a 404 error.</li>
            <li>The response includes joined data from related tables (routes, districts, seat_plans).</li>
        </ul>
    </div>
@endsection
