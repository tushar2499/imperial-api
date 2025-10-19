@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Coaches</h1>

        <h3>Request</h3>
        <p>Retrieve all coaches with a <strong>GET</strong> request:</p>
        <pre><code>GET /coaches</code></pre>

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
                        <td>Coach no to search</td>
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
  "message": "Coaches retrieved successfully",
  "data" : [
    "current_page": 1,
    "data": [
        {
        "id": 1,
        "coach_no": "C001",
        "seat_plan_id": 1,
        "coach_type": 1,
        "status": "active",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2020-01-01T12:00:00",
        "updated_at": "2020-01-01T12:00:00",
        "deleted_at": null
        }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/coaches?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/coaches?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/coaches?page=1",
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
    "path": "http://127.0.0.1:8000/api/coaches",
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
