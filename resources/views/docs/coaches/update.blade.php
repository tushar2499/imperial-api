@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Coach</h1>

        <h3>Request</h3>
        <p>Update the details of a specific coach by ID:</p>
        <pre><code>PUT /coaches/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "coach_no": "C002",
  "registration_number": "XYZ9876",
  "manufacturer_company": "Company C",
  "model_year": 2022,
  "chasis_no": "CH987654322",
  "engine_number": "EN987655",
  "country_of_origin": "Canada",
  "lc_code_number": "LC988",
  "delivery_to_dipo": "Dipo C",
  "delivery_date": "2022-05-01",
  "color": "Green",
  "seat_plan_id": 3,
  "coach_type": 1,
  "financed_by": "Bank C",
  "tennure_of_the_terms": 15,
  "status": "inactive",
  "updated_by": 3
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Coach updated successfully",
  "data": {
    "id": 2,
    "coach_no": "C002",
    "registration_number": "XYZ9876",
    "manufacturer_company": "Company C",
    "model_year": 2022,
    "chasis_no": "CH987654322",
    "engine_number": "EN987655",
    "country_of_origin": "Canada",
    "lc_code_number": "LC988",
    "delivery_to_dipo": "Dipo C",
    "delivery_date": "2022-05-01",
    "color": "Green",
    "seat_plan_id": 3,
    "coach_type": 1,
    "financed_by": "Bank C",
    "tennure_of_the_terms": 15,
    "status": "inactive",
    "created_by": 2,
    "updated_by": 3,
    "created_at": "2021-02-15T12:00:00",
    "updated_at": "2022-05-01T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
