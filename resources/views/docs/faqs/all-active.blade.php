@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Active Faqs</h1>

        <h3>Request</h3>
        <p>Retrieve all active faqs with a <strong>GET</strong> request:</p>
        <pre><code>GET /faqs/all-active</code></pre>



        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "message": "All active faqs retrieved successfully",
    "data" : [
        {
            "id": 1,
            "question": "Faq question",
            "answer": "Faq answer",
            "status": 1,
            "created_by": 1,
            "updated_by": null,
            "created_at": "2025-11-02T05:29:58.000000Z",
            "updated_at": "2025-11-02T05:29:58.000000Z",
            "deleted_at": null
        }
    ]

}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
