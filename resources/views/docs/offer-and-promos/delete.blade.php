@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Offer and promo</h1>

        <h3>Request</h3>
        <p>Soft delete a specific Offer and promo by ID:</p>
        <pre><code>DELETE /offer-and-promos/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
{
    "status": "success",
    "message": "Offer and promo deleted successfully"
}
                    </code>
                </pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The offer and promo ID is required in the URL to specify which offer and promo to delete.</li>
            <li>If the offer and promo does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
