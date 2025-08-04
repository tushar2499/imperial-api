@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Employee</h1>

        <h3>Request</h3>
        <p>Create a new employee with a <strong>POST</strong> request:</p>
        <pre><code>POST /employees</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
{
    "name": "XYZ",
    "contact_no": "01XXXXXXXX",
    "email": "xyz@example.com",
    "photo": "" (image file),
    "father_name": "XYZ Father",
    "mother_name": "XYZ Mother",
    "date_of_birth": "1990-04-01",
    "nid_or_passport_no": "XXXXXXXXXXXX",
    "nid_or_passport_no_image": "" (image file),
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
    "nominee_photo": "" (image file),
    "nominee_nid_or_passport_no": "XXXXXXXXXXXX",
    "nominee_relation": "Brother",
    "academics[0][degree]": "BSc",
    "academics[0][field_of_study]": "CES",
    "academics[0][institute]": "Dhaka University",
    "academics[0][passing_year]": "2020",
    "academics[0][grade]": "3.50",
    "experiences[0][organization]": "Shohag",
    "experiences[0][position]": "Computer Operator",
    "experiences[0][start_date]": "2020-01-01",
    "experiences[0][end_date]": "2025-01-01",
    "experiences[0][responsibility]": "Computer Operator",
}
                    </code>
                </pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "message": "Employee created successfully",
    "data": {
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
        "academics": [
            {
                "degree": "BSc",
                "field_of_study": "CES",
                "institute": "Dhaka University",
                "passing_year": "2020",
                "grade": "3.50"
            }
        ],
        "experiences": [
            {
                "organization": "Shohag",
                "position": "Computer Operator",
                "start_date": "2020-01-01",
                "end_date": "2025-01-01",
                "responsibility": "Computer Operator"
            }
        ],
        "status": 1,
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
