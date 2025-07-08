@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API Update District Documentation</h1>

        <h3>Update District Request</h3>
        <p>This is a <strong>PUT</strong> request to update an existing district. To update a district, make a request to the following endpoint:</p>
        <pre><code>PUT /api/districts/{id}</code></pre>

        <h4>Request Example:</h4>
        <div class="alert alert-info">
            The request requires the district ID in the URL path and the new district data in the request body.
        </div>
        <p>For example, to update the district with ID 2:</p>
        <pre><code>PUT /api/districts/2</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "name": "Updated District 1",
    "code": "UD1",
    "status": 1
}
                </code></pre>
            </div>
        </div>

        <h4>Successful Response (Status Code: 200):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "code": "200",
    "message": "District updated successfully",
    "data": {
        "id": 2,
        "name": "Updated District 1",
        "code": "UD1",
        "status": 1,
        "created_at": "2025-06-24 11:11:27",
        "updated_at": "2025-07-08 07:15:33"
    }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Explanation:</h3>
        <ul>
            <li><strong>status</strong>: Indicates the success or failure of the request. "success" indicates the district was successfully updated.</li>
            <li><strong>code</strong>: The HTTP status code. Here it’s `200`, indicating a successful update.</li>
            <li><strong>message</strong>: A human-readable message, confirming that the district was updated successfully.</li>
            <li><strong>data</strong>: Contains the updated district's information:
                <ul>
                    <li><strong>id</strong>: The unique ID of the district.</li>
                    <li><strong>name</strong>: The name of the district.</li>
                    <li><strong>code</strong>: The code assigned to the district.</li>
                    <li><strong>status</strong>: The status of the district (e.g., active or inactive).</li>
                    <li><strong>created_at</strong>: Timestamp when the district was created.</li>
                    <li><strong>updated_at</strong>: Timestamp when the district was last updated.</li>
                </ul>
            </li>
        </ul>

        <h4>Notes:</h4>
        <ul>
            <li>The <strong>id</strong> in the URL is required to specify which district to update.</li>
            <li>The request body must contain the updated information for the district (e.g., `name`, `code`, `status`).</li>
            <li>If the district with the given ID does not exist, an error response will be returned.</li>
            <li>In case of a successful update, the district's `updated_at` timestamp will reflect the most recent update time.</li>
        </ul>
    </div>
@endsection
