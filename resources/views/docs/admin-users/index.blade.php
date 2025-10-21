@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Admin Users</h1>

        <h3>Request</h3>
        <p>Retrieve all seat plans with a <strong>GET</strong> request:</p>
        <pre><code>GET /seat-plans</code></pre>

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
                        <td>Admin user name, email and mobile number to search</td>
                        <td></td>
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
  "message": "Admin users retrieved successfully",
  "data" : [
       "current_page": 1,
       "data": [
        {
            "id": 1,
            "counter_id": 1,
            "role": "admin",
            "user_name": "john_doe",
            "first_name": "John",
            "last_name": "Doe",
            "email": "a@b.com",
            "photo": null
            "mobile": "0123456789",
            "identity_type": "Passport",
            "identity_image": null,
            "date_of_birth": "1999-09-10",
            "country": null,
            "district_id": 1,
            "gender": "Male",
            "type": "1",
            "access_type": null,
            "account_type": null,
            "front_date": null,
            "back_date": null,
            "status": "1",
            "created_by": null,
            "updated_by": null,
            "email_verified_at": null,
            "sales_status": null,
            "booking_status": null,
            "block_status": null,
            "cancel_status": null,
            "created_at": "2025-09-12T08:10:47.000000Z",
            "updated_at": "2025-09-12T12:43:05.000000Z",
        }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/admin-users?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://127.0.0.1:8000/api/admin-users?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/admin-users?page=1",
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
    "path": "http://127.0.0.1:8000/api/admin-userss",
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
