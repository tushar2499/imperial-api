@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Fare</h1>

        <h3>Request</h3>
        <p>Create a new fare with a <strong>POST</strong> request:</p>
        <pre><code>POST /fares</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "route_id": 1,
  "seat_plan_id": 1,
  "coach_type": 1,
  "from_date": "2024-01-01",
  "to_date": "2024-12-31",
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
  "message": "Fare created successfully",
  "data": {
    "data": {
      "id": 3,
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
      "updated_by": null,
      "created_at": "2024-01-15T12:00:00",
      "updated_at": "2024-01-15T12:00:00",
      "deleted_at": null
    }
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
            <li>The response includes joined data from routes, districts, and seat_plans tables</li>
            <li>Coach type: 1 = AC, 2 = Non-AC</li>
            <li>If dates are not provided, the fare will be valid indefinitely</li>
        </ul>
    </div>
@endsection
