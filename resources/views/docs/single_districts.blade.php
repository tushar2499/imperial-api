@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API Single District Documentation</h1>

        <h3>Retrieve Single District Request</h3>
        <p>This is a <strong>GET</strong> request to retrieve a single district by its ID. Make a request to the following endpoint:</p>
        <pre><code>GET /api/districts/{id}</code></pre>

        <h4>Request Example:</h4>
        <div class="alert alert-info">
            The request requires the district ID in the URL path, and no request body is needed.
        </div>
        <p>For example, to retrieve the district with ID 2:</p>
        <pre><code>GET /api/districts/2</code></pre>

        <h4>Successful Response (Status Code: 200):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "code": 200,
    "message": "District retrieved successfully",
    "data": {
        "id": 2,
        "name": "Updated District 1",
        "code": "UD1"
    }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Explanation:</h3>
        <ul>
            <li><strong>status</strong>: Indicates the success or failure of the request. "success" means the district was successfully retrieved.</li>
            <li><strong>code</strong>: The HTTP status code. In this case, it’s `200`, indicating that the district was successfully retrieved.</li>
            <li><strong>message</strong>: A human-readable message about the result of the request, in this case, "District retrieved successfully".</li>
            <li><strong>data</strong>: Contains the retrieved district's information:
                <ul>
                    <li><strong>id</strong>: The unique ID of the district.</li>
                    <li><strong>name</strong>: The name of the district.</li>
                    <li><strong>code</strong>: The code assigned to the district.</li>
                </ul>
            </li>
        </ul>

        <h4>Notes:</h4>
        <ul>
            <li>The <strong>id</strong> in the URL is required to identify which district you want to retrieve.</li>
            <li>If the district with the given ID does not exist, an error response will be returned.</li>
            <li>The response will include the `status`, `code`, `message`, and `data` fields, where `data` contains the district’s details.</li>
        </ul>
    </div>
@endsection
