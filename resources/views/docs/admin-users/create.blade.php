@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create Admin Users</h1>

        <h3>Request</h3>
        <p>Create a new admin user by sending a <strong>POST</strong> request:</p>
        <pre><code>POST /admin-users</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "user_name": "john_doe",
  "first_name": "John",
  "last_name": "Doe",
  "mobile": "0123456789",
  "email": "a@b.com",
  "type": "1",
  "password": "secret",
  "gender": "male",
  "district_id": "1",
  "date_of_birth": null,
  "role": "admin",
  "counter_id": "1",
  "access_type": "1",
  "account_type": null,
  "front_date": null,
  "back_date": null,
  "identity_type": null,
  "identity_image": null,
  "sales_status": null,
  "booking_status": null,
  "block_status": null,
  "cancel_status": null,
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
    "message": "Admin user created successfully",
    "data": {
        "id": 1,
        "counter_id": 1,
        "role": "admin",
        "user_name": "john_doe",
        "first_name": "John",
        "last_name": "Doe",
        "email": "a@b.com",
        "photo": null
        "mobile": "0123456789",
        "identity_type": "Passport",
        "identity_image": null,
        "date_of_birth": "1999-09-10",
        "country": null,
        "district_id": 1,
        "gender": "Male",
        "type": "1",
        "access_type": null,
        "account_type": null,
        "front_date": null,
        "back_date": null,
        "status": "1",
        "created_by": null,
        "updated_by": null,
        "email_verified_at": null,
        "sales_status": null,
        "booking_status": null,
        "block_status": null,
        "cancel_status": null,
        "created_at": "2025-09-12T08:10:47.000000Z",
        "updated_at": "2025-09-12T12:43:05.000000Z",
    }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
