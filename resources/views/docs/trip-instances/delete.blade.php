@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Trip Instance</h1>

        <h3>Request</h3>
        <p>Delete a specific trip instance by ID:</p>
        <pre><code>DELETE /trip-instances/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "success": true,
  "message": "Trip Instance deleted successfully",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The trip instance ID is required in the URL to specify which configuration to delete.</li>
            <li>If the trip instance does not exist, the API will return a 404 error.</li>
            <li>This operation will also delete all associated boarding/dropping points.</li>
            <li>This is a permanent delete operation - the record will be completely removed from the database.</li>
            <li>Make sure to backup any important data before deletion.</li>
        </ul>

        <h3>Error Responses:</h3>
        <div class="card">
            <div class="card-header">
                <strong>404 Not Found</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "success": false,
  "message": "Trip Instance not found"
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>500 Server Error</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "success": false,
  "message": "Failed to delete trip instance: Database connection error"
}
                </code></pre>
            </div>
        </div>

        <h3>Cascade Effects:</h3>
        <ul>
            <li><strong>Boarding/Dropping Points</strong> - All related boarding and dropping points will be deleted</li>
            <li><strong>Active Bookings</strong> - Consider checking for active bookings before deletion</li>
            <li><strong>Historical Data</strong> - This operation may affect reporting and historical data</li>
        </ul>
    </div>
@endsection
