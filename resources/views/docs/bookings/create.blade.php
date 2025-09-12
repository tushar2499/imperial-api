@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Booking</h1>

        <h3>Request</h3>
        <p>Create a new booking with a <strong>POST</strong> request:</p>
        <pre><code>POST /bookings</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "mobile": "01712345678",
  "name": "John Doe",
  "gender": "Male",
  "age": 30,
  "address": "123 Main Street, Dhaka",
  "passport_no": null,
  "nid": "1234567890123",
  "nationality": "Bangladeshi",
  "email": "john@example.com",
  "trip_id": 1,
  "type": 2,
  "date": "2025-08-18",
  "time": "10:00:00",
  "route_id": 1,
  "boarding_id": 1,
  "dropping_id": 2,
  "booking_details": [
    {
      "seat_inventory_id": 1,
      "seat_id": 1,
      "price": 1500,
      "discount": 50
    },
    {
      "seat_inventory_id": 2,
      "seat_id": 2,
      "price": 1500,
      "discount": 0
    }
  ]
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response (201 Created):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 201,
  "message": "Booking created successfully and seats marked as sold",
  "data": {
    "booking": {
      "id": 5,
      "pnr_number": "L2DB92GFBG",
      "trip_id": 1,
      "type": 2,
      "date": "2025-08-18",
      "time": "10:00:00",
      "route_id": 1,
      "boarding_id": 1,
      "dropping_id": 2,
      "total_tickets": 2,
      "total_price": 3000,
      "total_discount": 50,
      "total_amount": 2950,
      "status": "confirmed",
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
      }
    },
    "sold_seats": [
      {
        "seat_inventory_id": 1,
        "seat_id": 1,
        "seat_number": "A1",
        "row_position": 1,
        "col_position": 1,
        "seat_type": "window",
        "price": 1500,
        "discount": 50,
        "amount": 1450,
        "status": "sold"
      },
      {
        "seat_inventory_id": 2,
        "seat_id": 2,
        "seat_number": "A2",
        "row_position": 1,
        "col_position": 2,
        "seat_type": "middle",
        "price": 1500,
        "discount": 0,
        "amount": 1500,
        "status": "sold"
      }
    ],
    "seat_status_summary": {
      "total_seats_sold": 2,
      "seat_status": "sold",
      "payment_status": "completed"
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>mobile</strong> - Required, string, max:20</li>
            <li><strong>name</strong> - Required, string, max:255</li>
            <li><strong>gender</strong> - Nullable, string, max:255</li>
            <li><strong>age</strong> - Nullable, numeric</li>
            <li><strong>address</strong> - Nullable, string, max:255</li>
            <li><strong>passport_no</strong> - Nullable, string, max:255</li>
            <li><strong>nid</strong> - Nullable, string, max:50</li>
            <li><strong>nationality</strong> - Nullable, string, max:255</li>
            <li><strong>email</strong> - Nullable, string, max:255</li>
            <li><strong>trip_id</strong> - Required, integer</li>
            <li><strong>type</strong> - Required, integer (2=Booked, 3=Blocked, 4=Sold)</li>
            <li><strong>date</strong> - Required, date format: Y-m-d</li>
            <li><strong>time</strong> - Required, time format: H:i:s</li>
            <li><strong>route_id</strong> - Required, integer, must exist in routes table</li>
            <li><strong>boarding_id</strong> - Nullable, integer, must exist in trip_boarding_droppings table</li>
            <li><strong>dropping_id</strong> - Nullable, integer, must exist in trip_boarding_droppings table</li>
            <li><strong>booking_details</strong> - Required, array</li>
            <li><strong>booking_details.*.seat_inventory_id</strong> - Required, integer</li>
            <li><strong>booking_details.*.seat_id</strong> - Required, integer, must exist in seats table</li>
            <li><strong>booking_details.*.price</strong> - Required, numeric</li>
            <li><strong>booking_details.*.discount</strong> - Nullable, numeric</li>
        </ul>

        <h3>Booking Types:</h3>
        <ul>
            <li><strong>Type 2 (Booked)</strong> - Regular booking with payment pending</li>
            <li><strong>Type 3 (Blocked)</strong> - Temporarily blocked for 5 minutes</li>
            <li><strong>Type 4 (Sold)</strong> - Confirmed booking with payment completed</li>
        </ul>

        <h3>Key Features:</h3>
        <ul>
            <li><strong>Customer Management</strong> - Creates or updates customer based on mobile number</li>
            <li><strong>PNR Generation</strong> - Automatically generates unique PNR number</li>
            <li><strong>Seat Inventory Update</strong> - Marks selected seats as sold/booked</li>
            <li><strong>Multi-seat Booking</strong> - Supports booking multiple seats in one transaction</li>
            <li><strong>Comprehensive Response</strong> - Returns complete trip, customer, and seat details</li>
            <li><strong>Boarding/Dropping Points</strong> - Includes counter and location information</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The system automatically finds or creates a customer based on the mobile number</li>
            <li>All selected seats must be available for booking</li>
            <li>PNR numbers are unique alphanumeric codes</li>
            <li>Booking details array allows multiple seats to be booked simultaneously</li>
            <li>Response includes complete trip information and boarding/dropping point details</li>
        </ul>
    </div>
@endsection
