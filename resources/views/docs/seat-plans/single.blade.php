@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Seat Plan by ID</h1>

        <h3>Request</h3>
        <p>Retrieve a specific seat plan by ID:</p>
        <pre><code>GET /seat-plans/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Seat plan retrieved successfully",
  "data": {
    "id": 1,
    "name": "Bus A",
    "floor": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-07-08T10:00:00",
    "updated_at": "2025-07-08T10:00:00",
    "floors": [
        {
            "id": 1,
            "seat_plan_id": 1,
            "name": "asdf",
            "layout_type": "2-2",
            "rows": 4,
            "cols": 4,
            "step": 1,
            "is_extra_seat": 0,
            "created_by": 1,
            "updated_by": null,
            "created_at": "2025-08-07 12:07:52",
            "updated_at": "2025-08-07 12:07:52",
            "seats": [
                {
                    "id": 1,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 1,
                    "col_position": 1,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 2,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 1,
                    "col_position": 2,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 3,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 1,
                    "col_position": 3,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 4,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 1,
                    "col_position": 4,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 5,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 2,
                    "col_position": 1,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 6,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 2,
                    "col_position": 2,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 7,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 2,
                    "col_position": 3,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 8,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 2,
                    "col_position": 4,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 9,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 3,
                    "col_position": 1,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 10,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 3,
                    "col_position": 2,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 11,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 3,
                    "col_position": 3,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 12,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 3,
                    "col_position": 4,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 13,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 4,
                    "col_position": 1,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 14,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 4,
                    "col_position": 2,
                    "seat_type": null,
                    "is_disable": 1,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 15,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 4,
                    "col_position": 3,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                },
                {
                    "id": 16,
                    "seat_plan_floor_id": 1,
                    "seat_plan_id": 1,
                    "seat_number": null,
                    "row_position": 4,
                    "col_position": 4,
                    "seat_type": null,
                    "is_disable": 0,
                    "status": "1",
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-08-07 12:07:52",
                    "updated_at": "2025-08-07 12:07:52"
                }
            ]
        }
    ]
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
