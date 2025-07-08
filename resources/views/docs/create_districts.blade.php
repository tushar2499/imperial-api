@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API Create District Documentation</h1>

        <h3>Create District Request</h3>
        <p>This is a <strong>POST</strong> request to create a new district. To create a new district, make a request to the following endpoint:</p>
        <pre><code>POST /api/districts</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "name": "New District",
    "code": "ND1",
    "status": 1
}
                </code></pre>
            </div>
        </div>

        <h4>Successful Response (Status Code: 201):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "code": 201,
    "message": "District created successfully",
    "data": {
        "data": {
            "id": 3,
            "name": "New District",
            "code": "ND1",
            "status": 1,
            "created_at": "2025-07-08 07:10:14",
            "updated_at": "2025-07-08 07:10:14"
        }
    }
}
                </code></pre>
            </div>
        </div>

        <h3>Response Explanation:</h3>
        <ul>
            <li><strong>status</strong>: Indicates the success or failure of the request. In this case, "success" indicates a successful creation.</li>
            <li><strong>code</strong>: The HTTP status code. Here it is `201`, indicating that the district was successfully created.</li>
            <li><strong>message</strong>: A human-readable message, confirming that the district was created successfully.</li>
            <li><strong>data</strong>: An object containing the created district information:
                <ul>
                    <li><strong>id</strong>: The unique ID assigned to the newly created district.</li>
                    <li><strong>name</strong>: The name of the new district.</li>
                    <li><strong>code</strong>: The code assigned to the new district.</li>
                    <li><strong>status</strong>: The status of the district (e.g., active or inactive).</li>
                    <li><strong>created_at</strong>: Timestamp when the district was created.</li>
                    <li><strong>updated_at</strong>: Timestamp when the district was last updated.</li>
                </ul>
            </li>
        </ul>

        <h4>Notes:</h4>
        <ul>
            <li>The <strong>name</strong>, <strong>code</strong>, and <strong>status</strong> fields are required to create a district.</li>
            <li>If the request is successful, a district object is returned with the details of the newly created district, including its `id`.</li>
            <li>In case of an error, an appropriate error message will be returned with a corresponding error code.</li>
        </ul>
    </div>
@endsection
