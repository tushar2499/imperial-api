@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Fares</h1>

        <h3>Request</h3>
        <p>Retrieve all fares with a <strong>GET</strong> request:</p>
        <pre><code>GET /fares</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Fares retrieved successfully",
  "data": [
    {
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
    },
    {
      "id": 2,
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
      "from_date": "2024-01-01",
      "to_date": "2024-12-31",
      "status": 1,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2024-01-01T12:00:00",
      "updated_at": "2024-01-01T12:00:00",
      "deleted_at": null
    }
  ]
}
                </code></pre>
            </div>
        </div>

        <h3>Response Fields:</h3>
        <ul>
            <li><strong>route_id</strong> - ID of the route</li>
            <li><strong>start_id, end_id</strong> - Start and end district IDs</li>
            <li><strong>start_name, end_name</strong> - Start and end district names</li>
            <li><strong>distance</strong> - Route distance in kilometers</li>
            <li><strong>duration</strong> - Estimated travel time</li>
            <li><strong>seat_plan_id</strong> - ID of the seat plan</li>
            <li><strong>seat_plan_name</strong> - Name of the seat plan</li>
            <li><strong>coach_type</strong> - Type of coach (1 = AC, 2 = Non-AC)</li>
            <li><strong>from_date, to_date</strong> - Validity period of the fare</li>
        </ul>
    </div>
@endsection
