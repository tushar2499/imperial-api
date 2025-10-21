@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Authenticated User Profile Photo</h1>

        <h3>Request</h3>
        <p>Update a authenticated user:</p>
        <pre><code>POST /profile/photo</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "photo": "file",
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
    "message": " Update authenticate user profile photo successfully",
    "data": file_url
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
