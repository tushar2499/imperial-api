@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Schedule</h1>

        <h3>Request</h3>
        <p>Create a new schedule with a <strong>POST</strong> request:</p>
        <pre><code>POST /schedules</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "name": "12:00 AM"
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
  "message": "Schedule created successfully",
  "data": {
    "data": {
      "id": 3,
      "name": "12:00 AM",
      "status": 1,
      "created_by": 1,
      "updated_by": null,
      "created_at": "2024-01-15T12:00:00",
      "updated_at": "2024-01-15T12:00:00",
      "deleted_at": null
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Validation Rules:</h3>
        <ul>
            <li><strong>name</strong> - Required, string, maximum 255 characters</li>
        </ul>
    </div>
@endsection
