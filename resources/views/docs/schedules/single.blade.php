@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Schedule</h1>

        <h3>Request</h3>
        <p>Retrieve a specific schedule by ID:</p>
        <pre><code>GET /schedules/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Schedule retrieved successfully",
  "data": {
    "id": 1,
    "name": "Morning Schedule",
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2024-01-01T12:00:00",
    "updated_at": "2024-01-01T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The schedule ID is required in the URL to specify which schedule to retrieve.</li>
            <li>If the schedule does not exist or has been deleted, the API will return a 404 error.</li>
        </ul>
    </div>
@endsection
