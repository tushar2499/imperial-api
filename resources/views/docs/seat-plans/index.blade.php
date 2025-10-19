@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Seat Plans</h1>

        <h3>Request</h3>
        <p>Retrieve all seat plans with a <strong>GET</strong> request:</p>
        <pre><code>GET /seat-plans</code></pre>

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
                        <td>Seat Plane name to search</td>
                        <td>Example District</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Seat plans retrieved successfully",
  "data" : [
       "current_page": 1,
       "data": [
        {
            "id": 1,
            "name": "Bus A",
            "floor": 1,
            "created_at": "2025-07-08T10:00:00",
            "updated_at": "2025-07-08T10:00:00",
            "floor_data" : [
                {
                    "id": 1,
                    "seat_plan_id": 1,
                    "name": "Lower",
                    "layout_type": "2-2",
                    "rows": 4,
                    "cols": 10,
                    "step": 2,
                    "is_extra_seat": 1,
                    "created_by": 1,
                    "updated_by": null,
                    "created_at": "2025-09-03T14:40:26.000000Z",
                    "updated_at": "2025-09-03T14:40:26.000000Z"
                }
            ]
        }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/seat-planes?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/seat-planes?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/seat-planes?page=1",
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
    "path": "http://127.0.0.1:8000/api/seat-planes",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
