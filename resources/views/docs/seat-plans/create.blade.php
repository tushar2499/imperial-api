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
  "floor": 1,
  "floors_data": [
    {
      "name": "Floor 1",
      "layoutType": "2-2",
      "rows": 4,
      "cols": 4,
      "step": 1,
      "extraSeat": 0,
      "seats": [
        { "seatName": "A1", "rowNumber": 1, "colNumber": 1, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "A2", "rowNumber": 1, "colNumber": 2, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "A3", "rowNumber": 1, "colNumber": 3, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "A4", "rowNumber": 1, "colNumber": 4, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "B1", "rowNumber": 2, "colNumber": 1, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "B2", "rowNumber": 2, "colNumber": 2, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "B3", "rowNumber": 2, "colNumber": 3, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "B4", "rowNumber": 2, "colNumber": 4, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "C1", "rowNumber": 3, "colNumber": 1, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "C2", "rowNumber": 3, "colNumber": 2, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "C3", "rowNumber": 3, "colNumber": 3, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "C4", "rowNumber": 3, "colNumber": 4, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "D1", "rowNumber": 4, "colNumber": 1, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "D2", "rowNumber": 4, "colNumber": 2, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "D3", "rowNumber": 4, "colNumber": 3, "seatType": "regular", "isDisable": 0, "status": 1 },
        { "seatName": "D4", "rowNumber": 4, "colNumber": 4, "seatType": "regular", "isDisable": 0, "status": 1 }
      ]
    }
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
    "code": 201,
    "message": "Seat plan and seats created successfully",
    "data": {
        "id": 1,
        "name": "Bus A",
        "floor": 1,
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-08-23 11:18:25",
        "updated_at": "2025-08-23 11:18:25",
        "floors": [
            {
                "id": 1,
                "seat_plan_id": 1,
                "name": "Floor 1",
                "layout_type": "2-2",
                "rows": 4,
                "cols": 4,
                "step": 1,
                "is_extra_seat": 0,
                "created_by": 1,
                "updated_by": null,
                "created_at": "2025-08-23 11:18:25",
                "updated_at": "2025-08-23 11:18:25",
                "seats": [
                    {
                        "id": 1,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "A1",
                        "row_position": 1,
                        "col_position": 1,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 2,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "A2",
                        "row_position": 1,
                        "col_position": 2,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 3,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "A3",
                        "row_position": 1,
                        "col_position": 3,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 4,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "A4",
                        "row_position": 1,
                        "col_position": 4,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 5,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "B1",
                        "row_position": 2,
                        "col_position": 1,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 6,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "B2",
                        "row_position": 2,
                        "col_position": 2,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 7,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "B3",
                        "row_position": 2,
                        "col_position": 3,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 8,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "B4",
                        "row_position": 2,
                        "col_position": 4,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 9,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "C1",
                        "row_position": 3,
                        "col_position": 1,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 10,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "C2",
                        "row_position": 3,
                        "col_position": 2,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 11,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "C3",
                        "row_position": 3,
                        "col_position": 3,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 12,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "C4",
                        "row_position": 3,
                        "col_position": 4,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 13,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "D1",
                        "row_position": 4,
                        "col_position": 1,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 14,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "D2",
                        "row_position": 4,
                        "col_position": 2,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 15,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "D3",
                        "row_position": 4,
                        "col_position": 3,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
                    },
                    {
                        "id": 16,
                        "seat_plan_floor_id": 1,
                        "seat_plan_id": 1,
                        "seat_number": "D4",
                        "row_position": 4,
                        "col_position": 4,
                        "seat_type": "regular",
                        "is_disable": 0,
                        "status": "1",
                        "created_by": 1,
                        "updated_by": null,
                        "created_at": "2025-08-23 11:18:25",
                        "updated_at": "2025-08-23 11:18:25"
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
