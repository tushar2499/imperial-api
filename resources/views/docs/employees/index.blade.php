@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Employees</h1>

        <h3>Request</h3>
        <p>Retrieve all employees with a <strong>GET</strong> request:</p>
        <pre><code>GET /employees</code></pre>

        <h3>Available Query Parameters:</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                     <tr>
                        <td><code>page</code></td>
                        <td>Integer</td>
                        <td>Number of page (min: 1)</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code>per_page</code></td>
                        <td>Integer</td>
                        <td>Number of items per page (max: 1000)</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td><code>search</code></td>
                        <td>String</td>
                        <td>Name, contact no and email to search</td>
                        <td>Search Word</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
    "status": "success",
    "message": "Employees retrieved successfully",
    "data" : [
        "current_page": 1,
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
        ],
        "first_page_url": "http://127.0.0.1:8000/api/employees?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://127.0.0.1:8000/api/employees?page=1",
        "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://127.0.0.1:8000/api/employees?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
        ],
        "next_page_url": null,
        "path": "http://127.0.0.1:8000/api/employees",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    ]

}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
