@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Authenticated User Password</h1>

        <h3>Request</h3>
        <p>Update a authenticated user:</p>
        <pre><code>POST /profile/password</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "current_password": "secret",
  "password": "new_secret",
  "password_confirmation": "new_secret",
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "code": 200,
    "message": "Update authenticate user password successfully",
    "data": []
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
