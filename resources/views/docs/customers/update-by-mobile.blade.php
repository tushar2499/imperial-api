@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Customer</h1>

        <h3>Request</h3>
        <p>Update a customer by ID using a <strong>PUT</strong> request:</p>
        <pre><code>PATCH /customers/{mobile}/update-by-mobile</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "name": "John Doe",
  "email": null,
  "age": null,
  "gender": null,
  "address": null,
  "passport_no": null,
  "nationality": null,
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
  "message": "Customer created successfully",
  "data": {
    "data": {
        "id": 1
        "mobile": "01XXXXXXXX",
        "name": "John Doe",
        "gender": null,
        "age": null,
        "address": null,
        "passport_no": null,
        "nid": null,
        "nationality": null,
        "email": null,
        "total_trips": 0,
        "total_tickets": 0,
        "total_cancelled_tickets": 0,
    },
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>name</strong> - Required, String, Length Max:255</li>
            <li><strong>email</strong> - Nullable, String, Length Max:255</li>
            <li><strong>age</strong> - Nullable, Integer</li>
            <li><strong>gender</strong> - Nullable, String, Length Max:255</li>
            <li><strong>address</strong> - Nullable, String, Length Max:255</li>
            <li><strong>passport_no</strong> - Nullable, String, Length Max:255</li>
            <li><strong>nid</strong> - Nullable, String, Length Max:255</li>
            <li><strong>nationality</strong> - Nullable, String, Length Max:255</li>
        </ul>
    </div>

@endsection
