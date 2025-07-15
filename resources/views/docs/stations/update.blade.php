@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Station</h1>

        <h3>Request</h3>
        <p>Update a specific station by ID:</p>
        <pre><code>PUT /stations/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "route_id": 102,
  "district_id": 6,
  "status": "inactive",
  "updated_by": 2
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
  "message": "Station updated successfully",
  "data": {
    "id": 3,
    "route_id": 102,
    "district_id": 6,
    "status": "inactive",
    "updated_by": 2,
    "created_at": "2025-07-08T12:00:00",
    "updated_at": "2025-07-08T14:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
