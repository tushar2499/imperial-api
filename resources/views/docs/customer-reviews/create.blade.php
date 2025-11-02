@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Customer review</h1>

        <h3>Request</h3>
        <p>Create a new customer review with a <strong>POST</strong> request:</p>
        <pre><code>POST /customer-reviews</code></pre>


        <h3>Request Body Parameters:</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Parameter</th>
                        <th width="50%">Validation Rule</th>
                        <th width="30%">Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>name</code></td>
                        <td>Required | String | Max: 255</td>
                        <td>Customer Name</td>
                    </tr>
                    <tr>
                        <td><code>date</code></td>
                        <td>Required | Date | Format: Y-m-d</td>
                        <td>2025-10-20</td>
                    </tr>
                    <tr>
                        <td><code>comment</code></td>
                        <td>Required | String</td>
                        <td>This is customer review</td>
                    </tr>
                    <tr>
                        <td><code>rating</code></td>
                        <td>Nullable | Integer | Min: 1 | Max: 5</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><code>image</code></td>
                        <td>Nullable | Image File | Max Size: 2MB | format: jpeg,png,jpg</td>
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
    "message": "Customer review created successfully",
    "data": {
        "id": 1,
        "name": "Customer Name",
        "date": "2025-10-20",
        "comment": "This is customer review",
        "rating": null,
        "image": null,
        "status": 1,
        "created_by": 1,
        "updated_by": null,
        "created_at": "2025-11-02T05:29:58.000000Z",
        "updated_at": "2025-11-02T05:29:58.000000Z",
        "deleted_at": null
    }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
