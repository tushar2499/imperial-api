@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Schedule</h1>

        <h3>Request</h3>
        <p>Soft delete a specific schedule by ID:</p>
        <pre><code>DELETE /schedules/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Schedule deleted successfully",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The schedule ID is required in the URL to specify which schedule to delete.</li>
            <li>If the schedule does not exist or has already been deleted, the API will return a 404 error.</li>
            <li>This is a soft delete operation - the schedule record remains in the database but is marked as deleted.</li>
            <li>After deletion, the schedule status is set to 0 and the deleted_at timestamp is set.</li>
        </ul>
    </div>
@endsection
