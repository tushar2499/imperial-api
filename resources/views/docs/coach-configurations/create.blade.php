@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Coach Configuration</h1>

        <h3>Request</h3>
        <p>Create a new coach configuration with a <strong>POST</strong> request:</p>
        <pre><code>POST /coach-configurations</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "coach_id": 1,
  "schedule_id": 2,
  "bus_id": 3,
  "seat_plan_id": 4,
  "route_id": 5,
  "coach_type": 1,
  "status": 1,
  "boarding_dropping_points": [
    {
      "counter_id": 1,
      "type": 1,
      "time": "08:00",
      "starting_point_status": true,
      "ending_point_status": false,
      "status": 1
    },
    {
      "counter_id": 2,
      "type": 1,
      "time": "08:30",
      "starting_point_status": false,
      "ending_point_status": false,
      "status": 1
    },
    {
      "counter_id": 3,
      "type": 2,
      "time": "14:00",
      "starting_point_status": false,
      "ending_point_status": false,
      "status": 1
    },
    {
      "counter_id": 4,
      "type": 2,
      "time": "14:30",
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
  "message": "Coach configuration created successfully",
  "data": {
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
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Field Definitions:</h3>
        <ul>
            <li><strong>coach_id</strong> - Required, must exist in coaches table</li>
            <li><strong>schedule_id</strong> - Required, must exist in schedules table</li>
            <li><strong>bus_id</strong> - Required, must exist in buses table</li>
            <li><strong>seat_plan_id</strong> - Required, must exist in seat_plans table</li>
            <li><strong>route_id</strong> - Required, must exist in routes table</li>
            <li><strong>coach_type</strong> - Required, 1 = AC, 2 = Non-AC</li>
            <li><strong>status</strong> - Optional, 1 = Active, 0 = Inactive (default: 1)</li>
        </ul>

        <h3>Boarding/Dropping Points:</h3>
        <ul>
            <li><strong>counter_id</strong> - Required, must exist in counters table</li>
            <li><strong>type</strong> - Required, 1 = Boarding, 2 = Dropping</li>
            <li><strong>time</strong> - Required, time in HH:MM format</li>
            <li><strong>starting_point_status</strong> - Boolean, true if this is the starting point</li>
            <li><strong>ending_point_status</strong> - Boolean, true if this is the ending point</li>
            <li><strong>status</strong> - Optional, 1 = Active, 0 = Inactive (default: 1)</li>
        </ul>
    </div>
@endsection
