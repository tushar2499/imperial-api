@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Customer reviews</h1>

        <h3>Request</h3>
        <p>Soft delete a specific Customer reviews by ID:</p>
        <pre><code>DELETE /customer-reviews/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
{
    "status": "success",
    "message": "Customer reviews deleted successfully"
}
                    </code>
                </pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The customer review ID is required in the URL to specify which customer reviews to delete.</li>
            <li>If the customer review does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
