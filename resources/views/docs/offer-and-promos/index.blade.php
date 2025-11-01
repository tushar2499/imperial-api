@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Employees</h1>

        <h3>Request</h3>
        <p>Retrieve all employees with a <strong>GET</strong> request:</p>
        <pre><code>GET /offer-and-promos</code></pre>

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
                        <td>Title, description and experience date to search</td>
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
    "message": "Offer and promos retrieved successfully",
    "data" : [
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "This is a test offer and promo",
                "expired_date": null,
                "description": null,
                "image": "http://127.0.0.1:8000/uploads/offer-and-promos/1762016881_K67aahLH22AWOFDhelFs.jpeg",
                "link": null,
                "status": 1,
                "created_by": 1,
                "updated_by": null,
                "created_at": "2025-11-01T17:08:01.000000Z",
                "updated_at": "2025-11-01T17:08:01.000000Z",
                "deleted_at": null
            }
        ],
        "first_page_url": "http://127.0.0.1:8000/api/offer-and-promos?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://127.0.0.1:8000/api/offer-and-promos?page=1",
        "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://127.0.0.1:8000/api/offer-and-promos?page=1",
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
        "path": "http://127.0.0.1:8000/api/offer-and-promos",
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
