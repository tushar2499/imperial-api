@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Schedules</h1>

        <h3>Request</h3>
        <p>Retrieve all schedules with a <strong>GET</strong> request:</p>
        <pre><code>GET /schedules</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Schedules retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "12:00 AM",
      "status": 1,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2024-01-01T12:00:00",
      "updated_at": "2024-01-01T12:00:00",
      "deleted_at": null
    },
    {
      "id": 2,
      "name": "01:00 PM",
      "status": 1,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2024-01-02T12:00:00",
      "updated_at": "2024-01-02T12:00:00",
      "deleted_at": null
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
