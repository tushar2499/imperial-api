@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Counters</h1>

        <h3>Request</h3>
        <p>Retrieve all counters with a <strong>GET</strong> request:</p>
        <pre><code>GET /counters</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Counters retrieved successfully",
  "data": [
    {
      "id": 1,
      "type": 1,
      "address": "123 Main Street",
      "land_mark": "Near Station",
      "location_url": "https://maps.example.com",
      "phone": "1234567890",
      "mobile": "9876543210",
      "email": "counter1@example.com",
      "primary_contact_no": "1122334455",
      "country": "Bangladesh",
      "district_id": 5,
      "booking_allowed_status": 1,
      "booking_allowed_class": 1,
      "no_of_boarding_allowed": 50,
      "sms_status": 1,
      "status": 1,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-07-15T10:00:00",
      "updated_at": "2025-07-15T10:00:00",
      "deleted_at": null
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
