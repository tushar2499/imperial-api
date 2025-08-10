@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Toggle Coach Configuration Status</h1>

        <h3>Request</h3>
        <p>Toggle the status of a specific coach configuration (active/inactive):</p>
        <pre><code>PATCH /coach-configurations/{id}/toggle-status</code></pre>

        <h4>Sample Request:</h4>
        <pre><code>PATCH /coach-configurations/1/toggle-status</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "success": true,
  "message": "Coach configuration status updated successfully",
  "data": {
    "id": 1,
    "coach_id": 1,
    "schedule_id": 2,
    "bus_id": 3,
    "seat_plan_id": 4,
    "route_id": 5,
    "coach_type": 1,
    "status": 0,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-08-10T10:00:00.000000Z",
    "updated_at": "2025-08-10T11:30:00.000000Z"
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Status Toggle Behavior:</h3>
        <ul>
            <li><strong>Active to Inactive</strong> - If current status is 1 (active), it will be changed to 0 (inactive)</li>
            <li><strong>Inactive to Active</strong> - If current status is 0 (inactive), it will be changed to 1 (active)</li>
            <li><strong>Automatic Update</strong> - The updated_at timestamp and updated_by field are automatically set</li>
            <li><strong>No Request Body</strong> - This endpoint doesn't require any request body data</li>
        </ul>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Quick Activation/Deactivation</strong> - Rapidly enable or disable coach configurations</li>
            <li><strong>Maintenance Mode</strong> - Temporarily disable configurations during maintenance</li>
            <li><strong>Emergency Suspension</strong> - Quickly deactivate configurations due to emergencies</li>
            <li><strong>Seasonal Operations</strong> - Enable/disable configurations based on seasonal demand</li>
            <li><strong>Admin Control</strong> - Provide simple toggle functionality in admin interfaces</li>
        </ul>

        <h3>Response Details:</h3>
        <ul>
            <li><strong>Updated Configuration</strong> - Returns the coach configuration with new status</li>
            <li><strong>Minimal Data</strong> - Only returns basic configuration data without related models</li>
            <li><strong>Audit Trail</strong> - Shows who updated the status and when</li>
            <li><strong>Status Confirmation</strong> - Clear indication of the new status value</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>The coach configuration ID is required in the URL path.</li>
            <li>If the coach configuration does not exist, the API will return a 404 error.</li>
            <li>This is a lightweight operation that only changes the status field.</li>
            <li>The operation is idempotent - multiple calls will toggle between states.</li>
            <li>Related boarding/dropping points are not affected by status changes.</li>
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
  "message": "Coach configuration not found"
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
  "message": "Failed to update coach configuration status: Database connection error"
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
