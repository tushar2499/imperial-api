@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API Districts Documentation</h1>

        <h3>Districts Request</h3>
        <p>This is a <strong>GET</strong> request to retrieve the list of districts. Make a request to the following endpoint:</p>
        <pre><code>GET /api/districts</code></pre>

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
                        <td>District name or code to search</td>
                        <td>Example District</td>
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
  "message": "Districts retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
            {
                "id": 1,
                "name": "Example District",
                "code": "12345",
            }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/districts?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/districts?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/districts?page=1",
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
    "path": "http://127.0.0.1:8000/api/districts",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Explanation:</h3>
        <ul>
            <li><strong>status</strong>: Indicates the success or failure of the request. For success, the value is "success".</li>
            <li><strong>code</strong>: The HTTP status code. In this case, it’s 200, indicating a successful request.</li>
            <li><strong>message</strong>: A human-readable message about the result of the request.</li>
            <li><strong>data</strong>: An array containing the district data. Each district object includes:
                <ul>
                    <li><strong>id</strong>: The unique ID of the district.</li>
                    <li><strong>name</strong>: The name of the district.</li>
                    <li><strong>code</strong>: A code assigned to the district.</li>
                </ul>
            </li>
        </ul>

        <h4>Notes:</h4>
        <ul>
            <li>This endpoint retrieves a list of districts. If no districts are available, the `data` array will be empty.</li>
            <li>Make sure the endpoint is authenticated (if required) or accessible via the proper headers (e.g., `Authorization`).</li>
        </ul>
    </div>
@endsection
