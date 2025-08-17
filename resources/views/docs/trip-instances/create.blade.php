@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Trip Instance</h1>

        <h3>Request</h3>
        <p>Create a new trip instance with a <strong>POST</strong> request:</p>
        <pre><code>POST /trip-instances</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "coach_id": 2,
  "bus_id": 1,
  "schedule_id": 1,
  "seat_plan_id": 1,
  "route_id": 1,
  "coach_type": 1,
  "trip_date": "2025-09-18 09:00:00"
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
  "code": 201,
  "message": "Trip instance created successfully",
  "data": {
    "data": {
      "trip_instance": {
        "coach_id": 2,
        "bus_id": 1,
        "schedule_id": 1,
        "seat_plan_id": 1,
        "route_id": 1,
        "coach_type": 1,
        "driver_id": null,
        "supervisor_id": null,
        "trip_date": "2025-09-28T00:00:00.000000Z",
        "status": 1,
        "migrated_trip_id": null,
        "created_by": 2,
        "id": 5,
        "updated_at": "2025-08-17T10:28:31.000000Z",
        "created_at": "2025-08-17T10:28:31.000000Z"
      },
      "seat_inventory_created": true,
      "seat_inventory": {
        "trip_id": 5,
        "total_seats": 24,
        "created_inventories": 24,
        "inventories": [
          {
            "trip_id": 5,
            "seat_id": 1,
            "booking_status": 1,
            "blocked_until": null,
            "booking_id": null,
            "last_locked_user_id": null,
            "created_by": 2,
            "id": 196,
            "updated_at": "2025-08-17T10:28:31.000000Z",
            "created_at": "2025-08-17T10:28:31.000000Z"
          },
          {
            "trip_id": 5,
            "seat_id": 2,
            "booking_status": 1,
            "blocked_until": null,
            "booking_id": null,
            "last_locked_user_id": null,
            "created_by": 2,
            "id": 198,
            "updated_at": "2025-08-17T10:28:31.000000Z",
            "created_at": "2025-08-17T10:28:31.000000Z"
          }
        ]
      }
    },
    "message": "Trip instance created successfully with seat inventory"
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>coach_id</strong> - Required, must exist in coaches table</li>
            <li><strong>bus_id</strong> - Required, must exist in buses table</li>
            <li><strong>schedule_id</strong> - Required, must exist in schedules table</li>
            <li><strong>seat_plan_id</strong> - Required, must exist in seat_plans table</li>
            <li><strong>route_id</strong> - Required, must exist in routes table</li>
            <li><strong>coach_type</strong> - Required, 1 = AC, 2 = Non-AC</li>
            <li><strong>trip_date</strong> - Required, valid date format (YYYY-MM-DD HH:MM:SS)</li>
        </ul>

        <h3>Automatic Features:</h3>
        <ul>
            <li><strong>Seat Inventory Creation</strong> - Automatically creates seat inventory for all seats in the seat plan</li>
            <li><strong>Booking Status</strong> - All seats start with booking_status = 1 (available)</li>
            <li><strong>Partition Management</strong> - Automatically handles database partitioning by month</li>
            <li><strong>User Tracking</strong> - Automatically sets created_by to the authenticated user</li>
        </ul>

        <h3>Response Components:</h3>
        <ul>
            <li><strong>trip_instance</strong> - The created trip instance details</li>
            <li><strong>seat_inventory_created</strong> - Boolean indicating if seat inventory was created</li>
            <li><strong>seat_inventory</strong> - Details about the created seat inventory including all individual seat records</li>
            <li><strong>total_seats</strong> - Total number of seats in the seat plan</li>
            <li><strong>created_inventories</strong> - Number of seat inventories successfully created</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>Creating a trip instance automatically generates seat inventory for booking management</li>
            <li>Each seat starts as available (booking_status = 1)</li>
            <li>The response includes complete seat inventory data for immediate use</li>
            <li>Trip instances are partitioned by month for performance optimization</li>
        </ul>
    </div>
@endsection
