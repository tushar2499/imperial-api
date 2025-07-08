@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get Specific Seat Plan by ID</h1>

        <h3>Request</h3>
        <p>Retrieve a specific seat plan by ID:</p>
        <pre><code>GET /seat-plans/{id}</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Seat plan retrieved successfully",
  "data": {
    "id": 1,
    "name": "Bus A",
    "rows": 5,
    "cols": 4,
    "layout_type": "2-2",
    "created_at": "2025-07-08T10:00:00",
    "updated_at": "2025-07-08T10:00:00"
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
