@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Refresh Token</h1>

        <h3>Request</h3>
        <p>This is a <strong>POST</strong> request to refresh the token for the authenticated user. You must be authenticated to call this endpoint.</p>
        <pre><code>POST /api/refresh-token</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Token refreshed successfully",
  "data": {
    "token" : "your_new_jwt_token_here"
  }
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
            <li>The refresh token action refreshes the user's new token.</li>
        </ul>
    </div>
@endsection
