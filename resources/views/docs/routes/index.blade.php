@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Routes</h1>

        <h3>Request</h3>
        <p>Retrieve all routes with a <strong>GET</strong> request:</p>
        <pre><code>GET /routes</code></pre>

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
                        <td>Start district name or End district name to search</td>
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
  "code": 200,
  "message": "Routes retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
            {
                "id": 1,
                "start_name": "District 1",
                "end_name": "District 2",
                "distance": 1300,
                "duration": "02:45",
                "is_popular": "0",
                "popular_position": null,
                "status": "1"
            },
    ],
    "first_page_url": "http://127.0.0.1:8000/api/routes?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/routes?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/routes?page=1",
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
    "path": "http://127.0.0.1:8000/api/routes",
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
