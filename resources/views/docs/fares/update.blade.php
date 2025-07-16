@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Fare</h1>

        <h3>Request</h3>
        <p>Update the details of a specific fare by ID:</p>
        <pre><code>PUT /fares/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "route_id": 2,
  "seat_plan_id": 2,
  "coach_type": 2,
  "from_date": "2024-02-01",
  "to_date": "2024-11-30",
  "status": 1
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Fare updated successfully",
  "data": {
    "id": 1,
    "route_id": 2,
    "start_id": 2,
    "end_id": 3,
    "start_name": "Chittagong",
    "end_name": "Sylhet",
    "distance": 189,
    "duration": "4:15",
    "route_status": 1,
    "seat_plan_id": 2,
    "seat_plan_name": "Non-AC",
    "coach_type": 2,
    "from_date": "2024-02-01",
    "to_date": "2024-11-30",
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2024-01-01T12:00:00",
    "updated_at": "2024-01-15T14:30:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>route_id</strong> - Required, must exist in routes table</li>
            <li><strong>seat_plan_id</strong> - Required, must exist in seat_plans table</li>
            <li><strong>coach_type</strong> - Required, integer, must be 1 (AC) or 2 (Non-AC)</li>
            <li><strong>from_date</strong> - Optional, valid date format</li>
            <li><strong>to_date</strong> - Optional, valid date format, must be after or equal to from_date</li>
            <li><strong>status</strong> - Optional, integer, must be 0 (inactive) or 1 (active), defaults to 1</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The fare ID is required in the URL to specify which fare to update.</li>
            <li>If the fare does not exist or has been deleted, the API will return a 404 error.</li>
            <li>All fields are required in the request body for a complete update.</li>
            <li>The response includes updated data joined from related tables.</li>
        </ul>
    </div>
@endsection
