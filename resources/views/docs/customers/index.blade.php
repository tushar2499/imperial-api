@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Customers</h1>

        <h3>Request</h3>
        <p>Retrieve all customers with a <strong>GET</strong> request:</p>
        <pre><code>GET /customers</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "message": "Customer retrieved successfully",
    "data": [
        {
            "id": 1
            "mobile": "01XXXXXXXX",
            "name": "John Doe",
            "gender": null,
            "age": null,
            "address": null,
            "passport_no": null,
            "nationality": null,
            "email": null,
            "total_trips": 0,
            "total_tickets": 0,
            "total_cancelled_tickets": 0,
        }
    ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
