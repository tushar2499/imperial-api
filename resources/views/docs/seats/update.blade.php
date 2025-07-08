@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update a Specific Seat by ID</h1>

        <h3>Request</h3>
        <p>Update a specific seat by its ID with a <strong>PUT</strong> request:</p>
        <pre><code>PUT /seats/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "seat_number": "2A",
  "row_position": 2,
  "col_position": 1,
  "seat_type": "VIP"
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
  "message": "Seat updated successfully",
  "data": {
    "seat_plan_id": 1,
    "seat_number": "2A",
    "row_position": 2,
    "col_position": 1,
    "seat_type": "VIP",
    "created_at": "2025-07-08T13:00:00",
    "updated_at": "2025-07-08T14:00:00"
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
