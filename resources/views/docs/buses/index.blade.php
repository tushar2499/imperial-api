@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Buses</h1>

        <h3>Request</h3>
        <p>Retrieve all buses with a <strong>GET</strong> request:</p>
        <pre><code>GET /buses</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
                    {
                        "status": "success",
                        "message": "Buses retrieved successfully",
                        "data": [
                            {
                            "id": 1,
                            "registration_number": "ABC1234",
                            "manufacturer_company": "Company A",
                            "model_year": 2020,
                            "chasis_no": "CH123456789",
                            "engine_number": "EN123456",
                            "country_of_origin": "Germany",
                            "lc_code_number": "LC123",
                            "delivery_to_dipo": "Dipo A",
                            "delivery_date": "2020-01-01",
                            "color": "Red",
                            "financed_by": "Bank A",
                            "tennure_of_the_terms": 10,
                            "status": "active",
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
