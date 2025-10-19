@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Stations</h1>

        <h3>Request</h3>
        <p>Retrieve all stations with a <strong>GET</strong> request:</p>
        <pre><code>GET /stations</code></pre>

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
                        <td>District name to search</td>
                        <td>Search Word</td>
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
  "message": "Stations retrieved successfully",
  "data" : [
    "current_page": 1,
    "data": [
        {
        "id": 1,
        "route_id": 1,
        "district_name": "District A",
        "district_id": 101,
        "status": "active",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-08T10:00:00",
        "updated_at": "2025-07-08T10:00:00",
        "deleted_at": null
        }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/stations?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/stations?page=1",
    "links": [
    {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
    },
    {
        "url": "http://127.0.0.1:8000/api/stations?page=1",
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
    "path": "http://127.0.0.1:8000/api/stations",
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
