@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Customer Review</h1>

        <h3>Request</h3>
        <p>Retrieve a specific customer review by ID:</p>
        <pre><code>GET /customer-reviews/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
{
    "status": "success",
    "message": "Customer Review retrieved successfully",
    "data": {
        "id": 1,
        "title": "This is a test customer review",
        "expired_date": null,
        "description": null,
        "image": "http://127.0.0.1:8000/uploads/offer-and-promos/1762016881_K67aahLH22AWOFDhelFs.jpeg",
        "link": null,
        "status": 1,
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-11-01T17:08:01.000000Z",
        "updated_at": "2025-11-01T17:08:01.000000Z",
        "deleted_at": null
    }
}
                    </code>
                </pre>
            </div>
        </div>
    </div>
@endsection
