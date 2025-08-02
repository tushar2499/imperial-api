@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Designations</h1>

        <h3>Request</h3>
        <p>Retrieve all buses with a <strong>GET</strong> request:</p>
        <pre><code>GET /buses</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
                    {
                        "status": "success",
                        "message": "Designations retrieved successfully",
                        "data": [
                            {
                            "id": 1,
                            "name": "Developer",
                            "status": 1,
                            "created_by": 1,
                            "updated_by": 1,
                            "created_at": "2020-01-01T12:00:00",
                            "updated_at": "2020-01-01T12:00:00",
                            "deleted_at": null
                            }
                        ]
                    }
                </code></pre>
            </div>
        </div>
    </div>
@endsection
