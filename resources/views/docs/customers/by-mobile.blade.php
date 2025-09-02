@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Customer</h1>

        <h3>Request</h3>
        <p>Retrieve a specific customer by Mobile:</p>
        <pre><code>GET /customers/{mobile}/by-mobile</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
{
    "status": "success",
    "message": "Customer retrieved successfully",
    "data": {
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
}
                    </code>
                </pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The customer mobile number is required in the URL to specify which customers to retrieve.</li>
            <li>If the customer does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
