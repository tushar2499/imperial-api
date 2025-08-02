@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Delete Designation</h1>

        <h3>Request</h3>
        <p>Soft delete a specific Designation by ID:</p>
        <pre><code>DELETE /designations/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
                        {
                            "status": "success",
                            "message": "Designation deleted successfully"
                        }
                    </code>
                </pre>
            </div>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>The designation ID is required in the URL to specify which designations to delete.</li>
            <li>If the designation does not exist, the API will return an error response.</li>
        </ul>
    </div>
@endsection
