@extends('layouts.app')
@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Bookings</h1>
        <h3>Request</h3>
        <p>Retrieve all bookings with a <strong>GET</strong> request:</p>
        <pre><code>GET /bookings</code></pre>

        <h3>Available Query Parameters:</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                     <tr>
                        <td><code>page</code></td>
                        <td>Integer</td>
                        <td>Number of page (min: 1)</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code>per_page</code></td>
                        <td>Integer</td>
                        <td>Number of items per page (max: 1000)</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td><code>search</code></td>
                        <td>String</td>
                        <td>Customer name or mobile number or pnr number</td>
                        <td>12344</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4>Sample Response (Comprehensive Format):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Bookings retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
            {
                "id": 1,
                "customer_id": 1,
                "pnr_number": "7COLZFJH1K",
                "trip_id": 2,
                "type": 0,
                "date": "2025-09-08",
                "time": "16:45:24",
                "note": null,
                "expire_date": null,
                "expire_time": null,
                "route_id": 1,
                "boarding_id": 7,
                "dropping_id": 10,
                "total_price": "0.000",
                "total_discount": "0.000",
                "total_amount": "0.000",
                "created_by": 2,
                "updated_by": null,
                "created_at": "2025-09-08T10:45:25.000000Z",
                "updated_at": "2025-09-08T10:45:25.000000Z",
                "customer": {
                    "id": 1,
                    "mobile": "01XXXXXXXXX",
                    "name": "User Name",
                    "gender": "male",
                    "age": 24,
                    "address": "Dhaka",
                    "passport_no": "B7654321",
                    "nid": null,
                    "nationality": "Bangladesh",
                    "email": "user@gmail.com",
                    "total_trips": 0,
                    "total_tickets": 0,
                    "total_cancelled_tickets": 0,
                    "status": 1,
                    "created_by": 2,
                    "updated_by": 2,
                    "created_at": "2025-09-04T05:30:34.000000Z",
                    "updated_at": "2025-09-14T12:23:09.000000Z",
                    "deleted_at": null
                },
                "boarding": {
                    "id": 7,
                    "trip_id": 2,
                    "counter_id": 4,
                    "type": 1,
                    "time": "01:00",
                    "starting_point_status": false,
                    "ending_point_status": false,
                    "status": 1,
                    "created_by": null,
                    "updated_by": null,
                    "created_at": "2025-09-08T09:56:58.000000Z",
                    "updated_at": "2025-09-08T09:56:58.000000Z"
                },
                "dropping": {
                    "id": 10,
                    "trip_id": 2,
                    "counter_id": 2,
                    "type": 2,
                    "time": "06:00",
                    "starting_point_status": false,
                    "ending_point_status": true,
                    "status": 1,
                    "created_by": null,
                    "updated_by": null,
                    "created_at": "2025-09-08T09:56:58.000000Z",
                    "updated_at": "2025-09-08T09:56:58.000000Z"
                },
                "route": {
                    "id": 1,
                    "start_id": 2,
                    "end_id": 3,
                    "distance": 400,
                    "duration": "05:04",
                    "status": "1",
                    "created_by": 2,
                    "updated_by": 2,
                    "created_at": "2025-09-03T11:43:09.000000Z",
                    "updated_at": "2025-09-10T06:35:40.000000Z",
                    "deleted_at": null
                },
                "booking_details": [
                    {
                        "id": 1,
                        "booking_id": 1,
                        "seat_inventory_id": 18,
                        "seat_id": 2,
                        "price": "600.000",
                        "discount": "50.000",
                        "amount": "550.000",
                        "created_by": null,
                        "updated_by": null,
                        "created_at": "2025-09-08T10:45:25.000000Z",
                        "updated_at": "2025-09-08T10:45:25.000000Z",
                        "seat": null
                    },
                    {
                        "id": 2,
                        "booking_id": 1,
                        "seat_inventory_id": 17,
                        "seat_id": 1,
                        "price": "600.000",
                        "discount": "50.000",
                        "amount": "550.000",
                        "created_by": null,
                        "updated_by": null,
                        "created_at": "2025-09-08T10:45:25.000000Z",
                        "updated_at": "2025-09-08T10:45:25.000000Z",
                        "seat": null
                    }
                ]
            }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/bookings?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/bookings?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/bookings?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": null,
    "path": "http://127.0.0.1:8000/api/bookings",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
                </code></pre>
            </div>
        </div>

    </div>
@endsection
