@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Coach Configurations by Schedule</h1>

        <h3>Request</h3>
        <p>Retrieve all coach configurations for a specific schedule:</p>
        <pre><code>GET /coach-configurations/schedule/{scheduleId}</code></pre>

        <h4>Sample Request:</h4>
        <pre><code>GET /coach-configurations/schedule/2</code></pre>

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
  ]
}
                </code></pre>
            </div>
        </div>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Schedule Management</strong> - View all coaches assigned to a specific schedule</li>
            <li><strong>Fleet Planning</strong> - Understand resource allocation for a particular time slot</li>
            <li><strong>Route Optimization</strong> - Analyze coach deployment across different routes within a schedule</li>
            <li><strong>Capacity Planning</strong> - Calculate total available seats for a schedule</li>
        </ul>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Multiple Configurations</strong> - Returns an array of all coach configurations for the schedule</li>
            <li><strong>Related Data</strong> - Includes coach, bus, seat_plan, route, and boarding_droppings</li>
            <li><strong>Schedule Information</strong> - The schedule details are not included since it's implicit from the request</li>
            <li><strong>Boarding Points</strong> - Shows all pickup and drop-off points for each configuration</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The schedule ID is required in the URL path.</li>
            <li>If the schedule does not exist, the API will return an empty array.</li>
            <li>Only active coach configurations are typically returned (status = 1).</li>
            <li>Results are ordered by coach configuration creation time.</li>
        </ul>
    </div>
@endsection
