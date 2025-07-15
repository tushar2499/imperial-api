@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Counter</h1>

        <h3>Request</h3>
        <p>Create a new counter with a <strong>POST</strong> request:</p>
        <pre><code>POST /counters</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "type": 1,
  "address": "456 Another Street",
  "land_mark": "Near Mall",
  "location_url": "https://maps.example.com",
  "phone": "1234567890",
  "mobile": "9876543210",
  "email": "counter2@example.com",
  "primary_contact_no": "2233445566",
  "country": "Bangladesh",
  "district_id": 2,
  "booking_allowed_status": 2,
  "booking_allowed_class": 3,
  "no_of_boarding_allowed": 60,
  "sms_status": 1
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
  "message": "Counter created successfully",
  "data": {
    "id": 2,
    "type": 1,
    "address": "456 Another Street",
    "land_mark": "Near Mall",
    "location_url": "https://maps.example.com",
    "phone": "1234567890",
    "mobile": "9876543210",
    "email": "counter2@example.com",
    "primary_contact_no": "2233445566",
    "country": "Bangladesh",
    "district_id": 2,
    "booking_allowed_status": 2,
    "booking_allowed_class": 3,
    "no_of_boarding_allowed": 60,
    "sms_status": 1,
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-07-15T12:00:00",
    "updated_at": "2025-07-15T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
