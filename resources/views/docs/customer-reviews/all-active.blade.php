@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Active Customer reviews</h1>

        <h3>Request</h3>
        <p>Retrieve all active customer reviews with a <strong>GET</strong> request:</p>
        <pre><code>GET /customer-reviews/all-active</code></pre>


        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "message": "All active customer reviews retrieved successfully",
    "data" : [
        {
            "id": 1,
            "name": "Customer Name",
            "date": "2025-10-20",
            "comment": "This is customer review",
            "rating": null,
            "image": null,
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
