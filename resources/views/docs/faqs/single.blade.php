@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Faq</h1>

        <h3>Request</h3>
        <p>Retrieve a specific faq by ID:</p>
        <pre><code>GET /faqs/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
{
    "status": "success",
    "message": "Faq retrieved successfully",
    "data": {
        "id": 1,
        "question": "Faq question",
        "answer": "Faq answer",
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
