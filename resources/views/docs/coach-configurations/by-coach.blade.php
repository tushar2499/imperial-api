@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Coach Configurations by Coach</h1>

        <h3>Request</h3>
        <p>Retrieve all coach configurations for a specific coach:</p>
        <pre><code>GET /coach-configurations/coach/{coachId}</code></pre>

        <h4>Sample Request:</h4>
        <pre><code>GET /coach-configurations/coach/1</code></pre>

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
    },
    {
      "id": 2,
      "coach_id": 1,
      "schedule_id": 3,
      "bus_id": 4,
      "seat_plan_id": 2,
      "route_id": 6,
      "coach_type": 2,
      "status": 1,
      "created_by": 1,
      "updated_by": null,
      "created_at": "2025-08-10T11:00:00.000000Z",
      "updated_at": "2025-08-10T11:00:00.000000Z",
      "schedule": {
        "id": 3,
        "name": "Evening Schedule",
        "departure_time": "18:00:00"
      },
      "bus": {
        "id": 4,
        "registration_number": "DH-789012",
        "model": "Scania K410"
      },
      "seat_plan": {
        "id": 2,
        "name": "3x2 Layout",
        "total_seats": 50
      },
      "route": {
        "id": 6,
        "name": "Dhaka - Sylhet",
        "distance": "247 km"
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
            <li><strong>Coach Utilization</strong> - View all schedules and routes where a specific coach is deployed</li>
            <li><strong>Performance Analysis</strong> - Analyze the performance of a particular coach across different configurations</li>
            <li><strong>Maintenance Planning</strong> - Check all active configurations before scheduling coach maintenance</li>
            <li><strong>Revenue Tracking</strong> - Track revenue generation from a specific coach across multiple routes</li>
        </ul>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Multiple Configurations</strong> - Returns an array of all configurations for the specified coach</li>
            <li><strong>Related Data</strong> - Includes schedule, bus, seat_plan, route, and boarding_droppings</li>
            <li><strong>Coach Information</strong> - The coach details are not included since it's implicit from the request</li>
            <li><strong>Historical Data</strong> - Shows both current and historical configurations</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The coach ID is required in the URL path.</li>
            <li>If the coach does not exist, the API will return an empty array.</li>
            <li>Results include all configurations regardless of status (active/inactive).</li>
            <li>Results are ordered by schedule and creation time.</li>
            <li>Useful for understanding coach deployment patterns and utilization rates.</li>
        </ul>
    </div>
@endsection
