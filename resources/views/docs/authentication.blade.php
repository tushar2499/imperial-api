@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API Login Documentation</h1>

        <h3>Login Request</h3>
        <p>To authenticate and receive a token, make a <strong>POST</strong> request to the following endpoint:</p>
        <pre><code>POST /api/login</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "user_name": "john_doe",
    "password": "password"
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
    "code": 200,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 2,
            "user_name": "john_doe",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "mobile": "1234567890",
            "status": "1",
            "created_at": "2025-06-24T04:26:31.000000Z"
        },
        "token": "your_jwt_token_here"
    }
}
                </code></pre>
            </div>
        </div>

        <h4>Error Response (Status Code: 401 - Unauthorized):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "error",
    "code": 401,
    "message": "Unauthorized",
    "data": null
}
                </code></pre>
            </div>
        </div>

        <h4>Error Response (Status Code: 422 - Validation Error):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "error",
    "code": 422,
    "message": "Validation error",
    "data": [
        "The password field is required."
    ]
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The <strong>user_name</strong> and <strong>password</strong> fields are required in the request body.</li>
            <li>In case of successful login, you will receive a JWT token that can be used for subsequent requests.</li>
            <li>If login fails, you will receive an <code>Unauthorized</code> error with a 401 status code.</li>
            <li>If validation errors occur (e.g., missing fields), a 422 error code is returned along with details of the missing fields.</li>
        </ul>
    </div>
@endsection
