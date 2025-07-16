@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create Stations</h1>

        <h3>Request</h3>
        <p>Create one or more stations with a <strong>POST</strong> request:</p>
        <pre><code>POST /stations</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code class="json">
{
  "route_id": 101,
  "district_id": [5, 6, 7]
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code class="json">
{
  "status": "success",
  "message": "Stations created successfully",
  "data": [
    {
      "id": 3,
      "route_id": 101,
      "district_id": 5,
      "status": 1,
      "created_by": 1,
      "updated_by": null,
      "created_at": "2025-07-08T12:00:00",
      "updated_at": "2025-07-08T12:00:00",
      "deleted_at": null
    },
    {
      "id": 4,
      "route_id": 101,
      "district_id": 6,
      "status": 1,
      "created_by": 1,
      "updated_by": null,
      "created_at": "2025-07-08T12:00:00",
      "updated_at": "2025-07-08T12:00:00",
      "deleted_at": null
    },
    {
      "id": 5,
      "route_id": 101,
      "district_id": 7,
      "status": 1,
      "created_by": 1,
      "updated_by": null,
      "created_at": "2025-07-08T12:00:00",
      "updated_at": "2025-07-08T12:00:00",
      "deleted_at": null
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
