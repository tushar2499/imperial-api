@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Coach</h1>

        <h3>Request</h3>
        <p>Retrieve a specific coach by ID:</p>
        <pre><code>GET /coaches/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Coach retrieved successfully",
  "data": {
    "id": 2,
    "coach_no": "C002",
    "registration_number": "XYZ9876",
    "manufacturer_company": "Company B",
    "model_year": 2021,
    "chasis_no": "CH987654321",
    "engine_number": "EN987654",
    "country_of_origin": "USA",
    "lc_code_number": "LC987",
    "delivery_to_dipo": "Dipo B",
    "delivery_date": "2021-02-15",
    "color": "Blue",
    "seat_plan_id": 2,
    "coach_type": 2,
    "financed_by": "Bank B",
    "tennure_of_the_terms": 12,
    "status": "active",
    "created_by": 2,
    "updated_by": 2,
    "created_at": "2021-02-15T12:00:00",
    "updated_at": "2021-02-15T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
