@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Counter</h1>

        <h3>Request</h3>
        <p>Update the details of a specific counter by ID:</p>
        <pre><code>PUT /counters/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "type": 2,
  "address": "789 New Street",
  "land_mark": "Near Park",
  "location_url": "https://maps.example.com",
  "phone": "1122334455",
  "mobile": "9988776655",
  "email": "counter2_updated@example.com",
  "primary_contact_no": "4455667788",
  "country": "Bangladesh",
  "district_id": 3,
  "booking_allowed_status": 3,
  "booking_allowed_class": 4,
  "no_of_boarding_allowed": 100,
  "sms_status": 2
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
  "message": "Counter updated successfully",
  "data": {
    "id": 2,
    "type": 2,
    "address": "789 New Street",
    "land_mark": "Near Park",
    "location_url": "https://maps.example.com",
    "phone": "1122334455",
    "mobile": "9988776655",
    "email": "counter2_updated@example.com",
    "primary_contact_no": "4455667788",
    "country": "Bangladesh",
    "district_id": 3,
    "booking_allowed_status": 3,
    "booking_allowed_class": 4,
    "no_of_boarding_allowed": 100,
    "sms_status": 2,
    "status": 0,
    "created_by": 1,
    "updated_by": 2,
    "created_at": "2025-07-15T12:00:00",
    "updated_at": "2025-07-15T14:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
