@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Stations</h1>

        <h3>Request</h3>
        <p>Retrieve all stations with a <strong>GET</strong> request:</p>
        <pre><code>GET /stations</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Stations retrieved successfully",
  "data": [
    {
      "id": 1,
      "route_id": 101,
      "district_id": 5,
      "status": "active",
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-07-08T10:00:00",
      "updated_at": "2025-07-08T10:00:00",
      "deleted_at": null
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
