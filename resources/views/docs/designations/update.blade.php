@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Bus</h1>

        <h3>Request</h3>
        <p>Update the details of a specific bus by ID:</p>
        <pre><code>PUT /buses/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
                        {
                            "name": "Developer",
                        }
                    </code>
                </pre>
            </div>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                 <pre>
                    <code>
                        {
                            "status": "success",
                            "message": "Designation updated successfully",
                            "data": {
                                "id": 1,
                                "name": "Developer",
                                "status": 1,
                                "created_by": 2,
                                "updated_by": 3,
                                "created_at": "2021-02-15T12:00:00",
                                "updated_at": "2022-05-01T12:00:00",
                                "deleted_at": null
                            }
                            }
                    </code>
                </pre>
            </div>
        </div>
    </div>
@endsection
