@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Employees</h1>

        <h3>Request</h3>
        <p>Retrieve all employees with a <strong>GET</strong> request:</p>
        <pre><code>GET /employees</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "message": "Employees retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "XYZ",
            "contact_no": "01XXXXXXXX",
            "email": "xyz@example.com",
            "photo": "",
            "father_name": "XYZ Father",
            "mother_name": "XYZ Mother",
            "date_of_birth": "1990-04-01",
            "nid_or_passport_no": "XXXXXXXXXXXX",
            "nid_or_passport_no_image": "",
            "job_type": "Full Time",
            "duty_hour": "8:30",
            "joining_date": "2020-01-01",
            "present_address": "Dhaka",
            "permanent_address": "Dhaka",
            "district_id": "1",
            "designation_id": "1",
            "license_category": "Heavy Vehicle Driver",
            "license_no": "XXXXXXXXXX",
            "license_expired_date": "2030-01-01",
            "religion": "Human",
            "blood_group": "A+",
            "marital_status": "Married",
            "reference_name": "ABC",
            "reference_contact_no": "01XXXXXXXXXX",
            "reference_remark": "",
            "nominee_name": "ABC",
            "nominee_contact_no": "01XXXXXXXXXX",
            "nominee_photo": "",
            "nominee_nid_or_passport_no": "XXXXXXXXXXXX",
            "nominee_relation": "Brother",
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
