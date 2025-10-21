@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Admin User</h1>

        <h3>Request</h3>
        <p>Soft delete a specific admin-user by ID:</p>
        <pre><code>DELETE /admin-users/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Seat plane deleted successfully",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The admin-user ID is required in the URL to specify which admin-user to delete.</li>
            <li>If the admin-user does not exist or has already been deleted, the API will return a 404 error.</li>
            <li>This is a soft delete operation - the admin-user record remains in the database but is marked as deleted.</li>
        </ul>
    </div>
@endsection
