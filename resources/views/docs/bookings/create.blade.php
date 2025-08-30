@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Booking</h1>

        <h3>Request</h3>
        <p>Create a new bookings with a <strong>POST</strong> request:</p>
        <pre><code>POST /bookings</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "mobile_number": "01XXXXXXXX",
  "name": "John Doe",
  "gender": null,
  "age": null,
  "address": null,
  "passport_no": null,
  "nationality": null,
  "email": null,
  "pnr_number": null,
  "trip_id": 1,
  "trip_date": "2025-08-18",
  "trip_time": "10:00:00",
  "route_id": 1,
  "boarding_id": 1,
  "dropping_id": 2,
  "booking_details": {
    {
      "seat_inventory_id": 1,
      "seat_id": 1,
      "price": 100,
      "discount": null,
    }
  },
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
  "message": "Booking created successfully",
  "data": {
    "data": {
        "customer_id": 1,
        "pnr_number": "L2DB92GFBG",
        "trip_id": "1",
        "trip_date": "2025-08-10",
        "trip_time": "10:00:00",
        "route_id": "1",
        "boarding_id": 1,
        "dropping_id": 2,
        "id": 5,
        "booking_details": [
            {
                "id": 3,
                "booking_id": 5,
                "seat_inventory_id": 1,
                "seat_id": 17,
                "price": "20.00",
                "discount": "0.00",
                "amount": "20.00",
            }
        ]
    },
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>mobile_number</strong> - Required, String, Length Max:255</li>
            <li><strong>name</strong> - Required, String, Length Max:255</li>
            <li><strong>gender</strong> - Nullable, String, Length Max:255</li>
            <li><strong>age</strong> - Nullable, Integer</li>
            <li><strong>address</strong> - Nullable, String, Length Max:255</li>
            <li><strong>passport_no</strong> - Nullable, String, Length Max:255</li>
            <li><strong>nationality</strong> - Nullable, String, Length Max:255</li>
            <li><strong>email</strong> - Nullable, String, Length Max:255</li>

            <li><strong>pnr_number</strong> - Nullable, Sting, Length Max:255, must unique in booking table</li>
            <li><strong>trip_id</strong> - Required, Integer</li>
            <li><strong>trip_date</strong> - Required, Date, Format: YYYY-MM-DD</li>
            <li><strong>trip_time</strong> - Required, Time, Format: HH:MM:SS</li>
            <li><strong>route_id</strong> - Required, Integer, Must exist in routes table</li>
            <li><strong>boarding_id</strong> - Nullable, Integer, Must exist in trip_boarding_droppings table</li>
            <li><strong>dropping_id</strong> - Nullable, Integer, Must exist in trip_boarding_droppings table</li>

            <li><strong>booking_details</strong> - Required, Array</li>
            <li><strong>booking_details.seat_inventory_id</strong> - Required, Integer</li>
            <li><strong>booking_details.seat_id</strong> - Required, Integer, Must exist in seats table</li>
            <li><strong>booking_details.price</strong> - Required, Numeric</li>
            <li><strong>booking_details.discount</strong> - Nullable, Numeric</li>
        </ul>
    </div>
@endsection
