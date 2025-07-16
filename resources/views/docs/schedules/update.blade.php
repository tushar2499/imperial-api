@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Schedule</h1>

        <h3>Request</h3>
        <p>Update the details of a specific schedule by ID:</p>
        <pre><code>PUT /schedules/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "name": "Updated Morning Schedule"
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
  "message": "Schedule updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Morning Schedule",
    "status": 1,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2024-01-01T12:00:00",
    "updated_at": "2024-01-15T14:30:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>name</strong> - Required, string, maximum 255 characters</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The schedule ID is required in the URL to specify which schedule to update.</li>
            <li>If the schedule does not exist or has been deleted, the API will return a 404 error.</li>
        </ul>
    </div>
@endsection
