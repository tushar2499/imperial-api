@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create Seat Plan with Seats</h1>

        <h3>Request</h3>
        <p>Create a new seat plan with seats by sending a <strong>POST</strong> request:</p>
        <pre><code>POST /seat-plans</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "name": "Bus A",
  "rows": 5,
  "cols": 4,
  "layout_type": "2-2",
  "seats": [
    { "seat_number": "1A", "row_position": 1, "col_position": 1, "seat_type": "regular" },
    { "seat_number": "1B", "row_position": 1, "col_position": 2, "seat_type": "regular" },
    { "seat_number": "1C", "row_position": 1, "col_position": 3, "seat_type": "VIP" }
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
  "message": "Seat plan and seats created successfully",
  "data": {
    "seat_plan": {
      "id": 1,
      "name": "Bus A",
      "rows": 5,
      "cols": 4,
      "layout_type": "2-2",
      "created_at": "2025-07-08T10:00:00",
      "updated_at": "2025-07-08T10:00:00"
    },
    "seats": [
      {
        "id": 1,
        "seat_plan_id": 1,
        "seat_number": "1A",
        "row_position": 1,
        "col_position": 1,
        "seat_type": "regular",
        "created_at": "2025-07-08T10:00:00",
        "updated_at": "2025-07-08T10:00:00"
      },
      {
        "id": 2,
        "seat_plan_id": 1,
        "seat_number": "1B",
        "row_position": 1,
        "col_position": 2,
        "seat_type": "regular",
        "created_at": "2025-07-08T10:00:00",
        "updated_at": "2025-07-08T10:00:00"
      },
      {
        "id": 3,
        "seat_plan_id": 1,
        "seat_number": "1C",
        "row_position": 1,
        "col_position": 3,
        "seat_type": "VIP",
        "created_at": "2025-07-08T10:00:00",
        "updated_at": "2025-07-08T10:00:00"
      }
    ]
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
