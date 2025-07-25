@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Station</h1>

        <h3>Request</h3>
        <p>Retrieve a specific station by ID:</p>
        <pre><code>GET /stations/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Station retrieved successfully",
  "data": {
    "id": 3,
    "route_id": 101,
    "district_id": 5,
    "status": "active",
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-07-08T12:00:00",
    "updated_at": "2025-07-08T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
