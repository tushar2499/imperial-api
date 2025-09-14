@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Trip Instance</h1>

        <h3>Request</h3>
        <p>Retrieve a specific trip instance by ID:</p>
        <pre><code>GET /trip-instances/{id}</code></pre>

        <h4>Sample Request:</h4>
        <pre><code>GET /trip-instances/4</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Trip instance retrieved successfully",
  "data": {
    "trip_instance": {
      "id": 4,
      "coach_id": 2,
      "bus_id": 1,
      "schedule_id": 1,
      "seat_plan_id": 1,
      "route_id": 1,
      "coach_type": 1,
      "driver_id": null,
      "supervisor_id": null,
      "trip_date": "2025-09-18T00:00:00.000000Z",
      "status": 1,
      "migrated_trip_id": null,
      "created_by": 2,
      "updated_by": null,
      "migrated_by": null,
      "created_at": "2025-08-14T10:24:12.000000Z",
      "updated_at": "2025-08-14T10:24:12.000000Z",
      "coach": {
        "id": 2,
        "coach_no": "101",
        "seat_plan_id": 1,
        "coach_type": "1",
        "status": 1
      },
      "bus": {
        "id": 1,
        "registration_number": "123",
        "manufacturer_company": "ad",
        "model_year": 2000,
        "chasis_no": "sad",
        "engine_number": "asd"
      },
      "schedule": {
        "id": 1,
        "name": "11:00",
        "status": 0
      },
      "seat_plan": {
        "id": 1,
        "name": "Bus A",
        "floor": 1,
        "rows": null,
        "cols": null,
        "layout_type": null,
        "floors": [
            {
                "id": 1,
                "seat_plan_id": 1,
                "name": "Lower",
                "layout_type": "2-2",
                "rows": 3,
                "cols": 4,
                "step": 2,
                "is_extra_seat": 1,
            }
        ],
      },
      "route": {
        "id": 1,
        "start_id": 2,
        "end_id": 2,
        "distance": 1300,
        "duration": "02:45",
        "status": "1"
      },
      "driver": null,
      "supervisor": null,
      "migrated_trip": null,
      "creator": {
        "id": 2,
        "user_name": "john_doe",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "mobile": "1234567890"
      },
      "updater": null,
      "migrator": null,
      "seat_inventory": [
        {
          "id": 148,
          "seat_id": 1,
          "booking_status": 1,
          "blocked_until": null,
          "booking_id": null,
          "last_locked_user_id": null,
          "seat_plan_floor_id": "1",
          "seat_number": "1A",
          "row_position": 1,
          "col_position": 1,
          "seat_type": "regular",
          "is_disable": 0
        },
        {
          "id": 150,
          "seat_id": 2,
          "booking_status": 1,
          "blocked_until": null,
          "booking_id": null,
          "last_locked_user_id": null,
          "seat_plan_floor_id": "1",
          "seat_number": "1B",
          "row_position": 1,
          "col_position": 2,
          "seat_type": "regular",
          "is_disable": 0
        },
        {
          "id": 152,
          "seat_id": 3,
          "booking_status": 1,
          "blocked_until": null,
          "booking_id": null,
          "last_locked_user_id": null,
          "seat_plan_floor_id": "1",
          "seat_number": "1C",
          "row_position": 1,
          "col_position": 3,
          "seat_type": "VIP",
          "is_disable": 0
        }
      ]
    },
    "partition_info": {
      "current_table": "trip_instances_202509",
      "trip_date": "2025-09-18",
      "partition_month": "2025-09",
      "seat_inventory_auto_created": false,
      "seat_inventory_count": 24
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Components:</h3>
        <ul>
            <li><strong>trip_instance</strong> - Complete trip details with all related data</li>
            <li><strong>Related Objects</strong> - Includes coach, bus, schedule, seat_plan, route details</li>
            <li><strong>Staff Information</strong> - Driver and supervisor details (if assigned)</li>
            <li><strong>User Tracking</strong> - Creator, updater, and migrator user information</li>
            <li><strong>seat_inventory</strong> - Complete seat inventory with booking status and seat details</li>
            <li><strong>partition_info</strong> - Database partition information and seat inventory metadata</li>
        </ul>

        <h3>Seat Inventory Details:</h3>
        <ul>
            <li><strong>booking_status</strong> - 1 = Available, 2 = Booked, 3 = Blocked</li>
            <li><strong>seat_type</strong> - regular, VIP, premium, etc.</li>
            <li><strong>seat_number</strong> - Human-readable seat identifier</li>
            <li><strong>row_position, col_position</strong> - Physical position in the bus layout</li>
            <li><strong>blocked_until</strong> - Timestamp until which seat is temporarily blocked</li>
            <li><strong>booking_id</strong> - Reference to booking if seat is booked</li>
            <li><strong>last_locked_user_id</strong> - User who last locked the seat for booking</li>
        </ul>

        <h3>Partition Information:</h3>
        <ul>
            <li><strong>current_table</strong> - The specific partition table being used</li>
            <li><strong>partition_month</strong> - Month-year partition identifier</li>
            <li><strong>seat_inventory_auto_created</strong> - Whether seat inventory was automatically created</li>
            <li><strong>seat_inventory_count</strong> - Total number of seats in the inventory</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The trip instance ID is required in the URL to specify which trip to retrieve.</li>
            <li>If the trip instance does not exist, the API will return a 404 error.</li>
            <li>The response includes complete seat inventory for booking management.</li>
            <li>Partition information helps understand the underlying data storage structure.</li>
            <li>All related objects are loaded for comprehensive trip management.</li>
        </ul>
    </div>
@endsection
