@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Fare</h1>

        <h3>Request</h3>
        <p>Retrieve a specific fare by ID:</p>
        <pre><code>GET /fares/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Fare retrieved successfully",
  "data": {
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
    "seat_type": "Business Class",
    "from_date": "2024-01-01",
    "to_date": "2024-12-31",
    "amount": 1500,
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2024-01-01T12:00:00",
    "updated_at": "2024-01-01T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Route Information</strong> - Includes start/end districts, distance, and duration</li>
            <li><strong>Seat Plan</strong> - Shows the associated seat plan name and ID</li>
            <li><strong>Coach Type</strong> - 1 for AC, 2 for Non-AC</li>
            <li><strong>Seat Type</strong> - Class of service (Suite Class, Business Class, Sleeper, Economy)</li>
            <li><strong>Amount</strong> - Fare price in smallest currency unit</li>
            <li><strong>Validity Period</strong> - from_date and to_date show when the fare is valid</li>
        </ul>

        <h3>Field Descriptions:</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>seat_type</td>
                        <td>String</td>
                        <td>Service class: Suite Class, Business Class, Sleeper, Economy</td>
                    </tr>
                    <tr>
                        <td>amount</td>
                        <td>Integer</td>
                        <td>Fare price in smallest currency unit (e.g., cents)</td>
                    </tr>
                    <tr>
                        <td>coach_type</td>
                        <td>Integer</td>
                        <td>1 = AC (Air Conditioned), 2 = Non-AC</td>
                    </tr>
                    <tr>
                        <td>from_date</td>
                        <td>Date</td>
                        <td>Start date of fare validity (null = no start limit)</td>
                    </tr>
                    <tr>
                        <td>to_date</td>
                        <td>Date</td>
                        <td>End date of fare validity (null = no end limit)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Fare Details</strong> - Get complete pricing information for booking</li>
            <li><strong>Route Planning</strong> - Check fare details for specific routes</li>
            <li><strong>Price Display</strong> - Show detailed fare breakdown to customers</li>
            <li><strong>Validation</strong> - Verify fare exists before processing bookings</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The fare ID is required in the URL to specify which fare to retrieve.</li>
            <li>If the fare does not exist or has been deleted, the API will return a 404 error.</li>
            <li>The response includes joined data from related tables (routes, districts, seat_plans).</li>
            <li>Amount represents the fare price in the smallest currency unit for precision.</li>
            <li>Seat type determines the service level and amenities provided.</li>
        </ul>
    </div>
@endsection
