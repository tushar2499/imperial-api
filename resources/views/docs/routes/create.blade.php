@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create Route</h1>

        <h3>Request</h3>
        <p>Create a new route with a <strong>POST</strong> request:</p>
        <pre><code>POST /routes</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "start_id": 2,
  "end_id": 2,
  "distance": 1300.3,
  "duration": "02:45"
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
  "message": "Route created successfully",
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
