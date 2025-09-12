@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Booking Details</h1>

        <h3>Request</h3>
        <p>Retrieve detailed booking information by booking ID:</p>
        <pre><code>GET /bookings/{id}</code></pre>

        <h4>Sample Request:</h4>
        <pre><code>GET /bookings/5</code></pre>

        <h4>Sample Response (200 OK):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Booking retrieved successfully",
  "data": {
    "booking": {
      "id": 5,
      "pnr_number": "L2DB92GFBG",
      "trip_id": 1,
      "date": "2025-08-18",
      "time": "10:00:00",
      "route_id": 1,
      "boarding_id": 1,
      "dropping_id": 2,
      "total_price": 3000,
      "total_discount": 50,
      "total_amount": 2950,
      "created_at": "2025-08-18T10:30:00.000000Z"
    },
    "customer": {
      "id": 1,
      "name": "John Doe",
      "mobile": "01712345678",
      "email": "john@example.com",
      "gender": "Male",
      "age": 30,
      "address": "123 Main Street, Dhaka",
      "passport_no": null,
      "nid": "1234567890123",
      "nationality": "Bangladeshi"
    },
    "boarding_info": {
      "id": 1,
      "trip_id": 1,
      "time": "08:00:00",
      "counter_id": 1,
      "counter_name": "Kallyanpur Counter",
      "counter_location": "Near Kallyanpur Bus Stand",
      "district_name": "Dhaka"
    },
    "dropping_info": {
      "id": 2,
      "trip_id": 1,
      "time": "14:00:00",
      "counter_id": 2,
      "counter_name": "GEC Counter",
      "counter_location": "GEC Circle",
      "district_name": "Chittagong"
    },
    "trip_details": {
      "trip_id": 1,
      "trip_date": "2025-08-18",
      "status": 1,
      "coach_type": 1,
      "coach_type_name": "AC",
      "coach": {
        "id": 1,
        "coach_no": "101",
        "status": 1
      },
      "bus": {
        "id": 1,
        "registration_number": "DH-123456",
        "manufacturer_company": "Volvo",
        "model_year": 2020
      },
      "route": {
        "id": 1,
        "start_id": 1,
        "end_id": 2,
        "distance": 264,
        "duration": "06:00"
      },
      "schedule": {
        "id": 1,
        "name": "10:00"
      },
      "seat_plan": {
        "id": 1,
        "name": "AC Business",
        "floor": 1,
        "rows": 10,
        "cols": 4
      },
      "driver": {
        "id": 1,
        "name": "Driver Name",
        "contact": "01712345678",
        "license": "DL123456"
      },
      "supervisor": {
        "id": 1,
        "name": "Supervisor Name",
        "contact": "01712345679"
      }
    },
    "booked_seats": [
      {
        "id": 3,
        "booking_id": 5,
        "seat_inventory_id": 1,
        "seat_id": 1,
        "price": "1500.00",
        "discount": "50.00",
        "amount": "1450.00",
        "seat": {
          "id": 1,
          "seat_number": "A1",
          "row_position": 1,
          "col_position": 1,
          "seat_type": "window"
        }
      },
      {
        "id": 4,
        "booking_id": 5,
        "seat_inventory_id": 2,
        "seat_id": 2,
        "price": "1500.00",
        "discount": "0.00",
        "amount": "1500.00",
        "seat": {
          "id": 2,
          "seat_number": "A2",
          "row_position": 1,
          "col_position": 2,
          "seat_type": "middle"
        }
      }
    ]
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Structure:</h3>
        <ul>
            <li><strong>booking</strong> - Core booking information including PNR, dates, and totals</li>
            <li><strong>customer</strong> - Complete customer details</li>
            <li><strong>boarding_info</strong> - Pickup point details with counter information</li>
            <li><strong>dropping_info</strong> - Drop-off point details with counter information</li>
            <li><strong>trip_details</strong> - Comprehensive trip information including coach, bus, route, schedule</li>
            <li><strong>booked_seats</strong> - Array of all booked seats with pricing and seat details</li>
        </ul>

        <h3>Key Information:</h3>
        <ul>
            <li><strong>PNR Number</strong> - Unique booking identifier for customer reference</li>
            <li><strong>Total Calculations</strong> - Shows price, discount, and final amount</li>
            <li><strong>Boarding/Dropping Points</strong> - Includes counter names, locations, and timing</li>
            <li><strong>Seat Details</strong> - Complete information about each booked seat</li>
            <li><strong>Trip Information</strong> - Full details about the journey including driver and supervisor</li>
        </ul>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Customer Service</strong> - Retrieve booking details for customer inquiries</li>
            <li><strong>Check-in Process</strong> - Verify booking information at boarding points</li>
            <li><strong>Trip Management</strong> - View passenger details for trip planning</li>
            <li><strong>Billing Inquiries</strong> - Access pricing and discount information</li>
            <li><strong>Ticket Verification</strong> - Confirm booking validity using PNR</li>
        </ul>

        <h3>Error Responses:</h3>
        <div class="card">
            <div class="card-header">
                <strong>404 Not Found - Booking Not Found</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 404,
  "message": "Booking not found",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The booking ID is required in the URL to specify which booking to retrieve</li>
            <li>Response includes complete trip information loaded from partitioned trip instances</li>
            <li>Boarding and dropping info includes counter details and district information</li>
            <li>Driver and supervisor information is included when available</li>
            <li>Seat details include position information for seat map display</li>
        </ul>
    </div>
@endsection
