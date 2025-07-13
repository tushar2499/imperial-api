@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Logout</h1>

        <h3>Request</h3>
        <p>This is a <strong>POST</strong> request to log out the authenticated user. You must be authenticated to call this endpoint.</p>
        <pre><code>POST /api/logout</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 401,
  "message": "Unauthenticated",
  "data": "Unauthenticated request. Please log in to access this resource."
}
                </code></pre>
            </div>
        </div>

        <h3>Response Explanation:</h3>
        <ul>
            <li><strong>status</strong>: The status of the request. "error" indicates the user is not authenticated.</li>
            <li><strong>code</strong>: The HTTP status code. Here it's `401`, indicating that the request is unauthorized.</li>
            <li><strong>message</strong>: A message explaining why the request failed (in this case, due to unauthenticated access).</li>
            <li><strong>data</strong>: A description providing additional details on why the request was unsuccessful (in this case, the user needs to be logged in).</li>
        </ul>

        <h4>Notes:</h4>
        <ul>
            <li>You must be logged in to access this endpoint. If you're not authenticated, you will receive a `401 Unauthorized` response.</li>
            <li>The logout action invalidates the user's session or token.</li>
        </ul>
    </div>
@endsection
