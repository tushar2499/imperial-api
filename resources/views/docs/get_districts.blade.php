@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API Districts Documentation</h1>

        <h3>Districts Request</h3>
        <p>This is a <strong>GET</strong> request to retrieve the list of districts. Make a request to the following endpoint:</p>
        <pre><code>GET /api/districts</code></pre>

        <h4>Request Example:</h4>
        <div class="alert alert-info">
            No request body is required for this endpoint. Simply send a GET request to the endpoint.
        </div>

        <h4>Successful Response (Status Code: 200):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "code": 200,
    "message": "Districts retrieved successfully",
    "data": [
        {
            "id": 2,
            "name": "Updated District 1",
            "code": "UD1"
        }
    ]
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
