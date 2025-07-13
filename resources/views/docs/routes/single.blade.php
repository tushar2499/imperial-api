@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Route</h1>

        <h3>Request</h3>
        <p>Retrieve a specific route by ID:</p>
        <pre><code>GET /routes/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Route retrieved successfully",
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
