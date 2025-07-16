@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Fare</h1>

        <h3>Request</h3>
        <p>Soft delete a specific fare by ID:</p>
        <pre><code>DELETE /fares/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Fare deleted successfully",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The fare ID is required in the URL to specify which fare to delete.</li>
            <li>If the fare does not exist or has already been deleted, the API will return a 404 error.</li>
            <li>This is a soft delete operation - the fare record remains in the database but is marked as deleted.</li>
            <li>After deletion, the fare will not appear in listing or single fare queries.</li>
            <li>The deleted_at timestamp is set to mark the fare as deleted.</li>
        </ul>

        <h3>Error Responses:</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "message": "Fare not found"
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
