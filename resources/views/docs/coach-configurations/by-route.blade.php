@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Coach Configurations by Route</h1>

        <h3>Request</h3>
        <p>Retrieve all coach configurations for a specific route:</p>
        <pre><code>GET /coach-configurations/route/{routeId}</code></pre>

        <h4>Sample Request:</h4>
        <pre><code>GET /coach-configurations/route/5</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "success": true,
  "message": "Coach configurations retrieved successfully",
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
        },
        {
          "id": 2,
          "coach_configuration_id": 1,
          "counter_id": 4,
          "type": 2,
          "time": "14:30:00",
          "starting_point_status": 0,
          "ending_point_status": 1,
          "status": 1,
          "counter": {
            "id": 4,
            "name": "New Market Counter",
            "location": "New Market, Chittagong"
          }
        }
      ]
    },
    {
      "id": 3,
      "coach_id": 2,
      "schedule_id": 4,
      "bus_id": 5,
      "seat_plan_id": 3,
      "route_id": 5,
      "coach_type": 2,
      "status": 1,
      "created_by": 1,
      "updated_by": null,
      "created_at": "2025-08-10T12:00:00.000000Z",
      "updated_at": "2025-08-10T12:00:00.000000Z",
      "coach": {
        "id": 2,
        "name": "Economy Coach 002",
        "code": "EC002"
      },
      "schedule": {
        "id": 4,
        "name": "Afternoon Schedule",
        "departure_time": "14:00:00"
      },
      "bus": {
        "id": 5,
        "registration_number": "DH-345678",
        "model": "Ashok Leyland"
      },
      "seat_plan": {
        "id": 3,
        "name": "3x2 Economy",
        "total_seats": 52
      },
      "boarding_droppings": []
    }
  ]
}
                </code></pre>
            </div>
        </div>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Route Analysis</strong> - View all coaches operating on a specific route</li>
            <li><strong>Capacity Planning</strong> - Calculate total seating capacity for a route across all schedules</li>
            <li><strong>Service Frequency</strong> - Understand how often coaches run on a particular route</li>
            <li><strong>Competition Analysis</strong> - Compare different coach types and schedules on the same route</li>
        </ul>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Multiple Configurations</strong> - Returns an array of all configurations for the specified route</li>
            <li><strong>Related Data</strong> - Includes coach, schedule, bus, seat_plan, and boarding_droppings</li>
            <li><strong>Route Information</strong> - The route details are not included since it's implicit from the request</li>
            <li><strong>Schedule Variety</strong> - Shows different schedules operating on the same route</li>
            <li><strong>Coach Types</strong> - Displays mix of AC and Non-AC coaches on the route</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The route ID is required in the URL path.</li>
            <li>If the route does not exist, the API will return an empty array.</li>
            <li>Results include all configurations regardless of status (active/inactive).</li>
            <li>Results are ordered by schedule departure time and coach type.</li>
            <li>Useful for route performance analysis and capacity optimization.</li>
        </ul>
    </div>
@endsection
