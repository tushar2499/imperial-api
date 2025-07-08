@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Seat Plan by ID</h1>

        <h3>Request</h3>
        <p>Update a specific seat plan by ID:</p>
        <pre><code>PUT /seat-plans/{id}</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "name": "Updated Bus A",
  "rows": 6,
  "cols": 4,
  "layout_type": "3-3"
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
  "message": "Seat plan updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Bus A",
    "rows": 6,
    "cols": 4,
    "layout_type": "3-3",
    "created_at": "2025-07-08T10:00:00",
    "updated_at": "2025-07-08T12:00:00"
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
