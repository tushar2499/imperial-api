@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Counter</h1>

        <h3>Request</h3>
        <p>Soft delete a specific counter by ID:</p>
        <pre><code>DELETE /counters/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Counter deleted successfully"
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The counter ID is required in the URL to specify which counter to delete.</li>
            <li>If the counter does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
