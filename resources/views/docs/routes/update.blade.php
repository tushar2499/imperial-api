@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Route</h1>

        <h3>Request</h3>
        <p>Update a specific route by ID:</p>
        <pre><code>PUT /routes/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "start_name": "Updated District 1",
  "end_name": "Updated District 1",
  "distance": 1300,
  "duration": "02:45",
  "status": "1"
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
  "message": "Route updated successfully",
  "data": {
    "id": 1,
    "start_name": "Updated District 1",
    "end_name": "Updated District 1",
    "distance": 1300,
    "duration": "02:45",
    "status": "1"
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
