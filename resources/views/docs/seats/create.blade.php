@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create Seats Under a Seat Plan</h1>

        <h3>Request</h3>
        <p>Create multiple seats under an existing seat plan with a <strong>POST</strong> request:</p>
        <pre><code>POST /seats</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "seat_plan_id": 1,
  "seats": [
    { "seat_number": "2A", "row_position": 2, "col_position": 1, "seat_type": "regular" },
    { "seat_number": "2B", "row_position": 2, "col_position": 2, "seat_type": "regular" },
    { "seat_number": "2C", "row_position": 2, "col_position": 3, "seat_type": "VIP" }
  ]
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
  "message": "Seats created successfully",
  "data": [
    {
      "seat_plan_id": 1,
      "seat_number": "2A",
      "row_position": 2,
      "col_position": 1,
      "seat_type": "regular",
      "created_at": "2025-07-08T13:00:00",
      "updated_at": "2025-07-08T13:00:00"
    },
    {
      "seat_plan_id": 1,
      "seat_number": "2B",
      "row_position": 2,
      "col_position": 2,
      "seat_type": "regular",
      "created_at": "2025-07-08T13:00:00",
      "updated_at": "2025-07-08T13:00:00"
    },
    {
      "seat_plan_id": 1,
      "seat_number": "2C",
      "row_position": 2,
      "col_position": 3,
      "seat_type": "VIP",
      "created_at": "2025-07-08T13:00:00",
      "updated_at": "2025-07-08T13:00:00"
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
