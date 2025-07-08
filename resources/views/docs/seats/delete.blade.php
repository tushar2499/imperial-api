@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete a Specific Seat by ID</h1>

        <h3>Request</h3>
        <p>Delete a specific seat by its ID with a <strong>DELETE</strong> request:</p>
        <pre><code>DELETE /seats/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Seat deleted successfully"
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The seat ID is required in the URL to specify which seat to delete.</li>
            <li>If the seat does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
