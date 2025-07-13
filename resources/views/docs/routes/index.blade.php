@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Routes</h1>

        <h3>Request</h3>
        <p>Retrieve all routes with a <strong>GET</strong> request:</p>
        <pre><code>GET /routes</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Routes retrieved successfully",
  "data": [
    {
      "id": 1,
      "start_name": "Updated District 1",
      "end_name": "Updated District 1",
      "distance": 1300,
      "duration": "02:45",
      "status": "1"
    },
    {
      "id": 2,
      "start_name": "District A",
      "end_name": "District B",
      "distance": 1500,
      "duration": "03:00",
      "status": "1"
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
