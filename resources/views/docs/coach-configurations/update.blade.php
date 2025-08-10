@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Coach Configuration</h1>

        <h3>Request</h3>
        <p>Update the details of a specific coach configuration by ID:</p>
        <pre><code>PUT /coach-configurations/{id}</code></pre>

        <h4>Request Body (Update with new boarding/dropping points):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "coach_type": 2,
  "status": 1,
  "boarding_dropping_points": [
    {
      "id": 1,
      "counter_id": 1,
      "type": 1,
      "time": "09:00",
      "starting_point_status": true,
      "ending_point_status": false,
      "status": 1
    },
    {
      "counter_id": 5,
      "type": 1,
      "time": "09:30",
      "starting_point_status": false,
      "ending_point_status": false,
      "status": 1
    },
    {
      "counter_id": 6,
      "type": 2,
      "time": "15:00",
      "starting_point_status": false,
      "ending_point_status": true,
      "status": 1
    }
  ]
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "success": true,
  "message": "Coach configuration updated successfully",
  "data": {
    "id": 1,
    "coach_id": 1,
    "schedule_id": 2,
    "bus_id": 3,
    "seat_plan_id": 4,
    "route_id": 5,
    "coach_type": 2,
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-08-10T10:00:00.000000Z",
    "updated_at": "2025-08-10T11:00:00.000000Z",
    "boarding_droppings": [
      {
        "id": 1,
        "coach_configuration_id": 1,
        "counter_id": 1,
        "type": 1,
        "time": "09:00:00",
        "starting_point_status": 1,
        "ending_point_status": 0,
        "status": 1,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-08-10T10:00:00.000000Z",
        "updated_at": "2025-08-10T11:00:00.000000Z"
      },
      {
        "id": 5,
        "coach_configuration_id": 1,
        "counter_id": 5,
        "type": 1,
        "time": "09:30:00",
        "starting_point_status": 0,
        "ending_point_status": 0,
        "status": 1,
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-08-10T11:00:00.000000Z",
        "updated_at": "2025-08-10T11:00:00.000000Z"
      },
      {
        "id": 6,
        "coach_configuration_id": 1,
        "counter_id": 6,
        "type": 2,
        "time": "15:00:00",
        "starting_point_status": 0,
        "ending_point_status": 1,
        "status": 1,
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-08-10T11:00:00.000000Z",
        "updated_at": "2025-08-10T11:00:00.000000Z"
      }
    ]
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Update Behavior:</h3>
        <ul>
            <li><strong>Existing Boarding/Dropping Points</strong> - Include "id" field to update existing points</li>
            <li><strong>New Boarding/Dropping Points</strong> - Omit "id" field to create new points</li>
            <li><strong>Remove Points</strong> - Points not included in the request will be deleted</li>
            <li><strong>Core Fields</strong> - coach_id, schedule_id, bus_id, seat_plan_id, route_id cannot be updated</li>
        </ul>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>coach_type</strong> - Optional, 1 = AC, 2 = Non-AC</li>
            <li><strong>status</strong> - Optional, 1 = Active, 0 = Inactive</li>
            <li><strong>boarding_dropping_points</strong> - Array of boarding/dropping points</li>
            <li><strong>time</strong> - Must be in HH:MM format</li>
            <li><strong>type</strong> - 1 = Boarding, 2 = Dropping</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The coach configuration ID is required in the URL to specify which configuration to update.</li>
            <li>If the coach configuration does not exist, the API will return a 404 error.</li>
            <li>The boarding_dropping_points array completely replaces existing points.</li>
            <li>Each boarding/dropping point with an ID will be updated, those without will be created.</li>
        </ul>
    </div>
@endsection
