@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Route</h1>

        <h3>Request</h3>
        <p>Delete a specific route by ID:</p>
        <pre><code>DELETE /routes/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Route deleted successfully"
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The route ID is required in the URL to specify which route to delete.</li>
            <li>If the route does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
