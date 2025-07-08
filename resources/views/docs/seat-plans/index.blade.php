@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Get All Seat Plans</h1>

        <h3>Request</h3>
        <p>Retrieve all seat plans with a <strong>GET</strong> request:</p>
        <pre><code>GET /seat-plans</code></pre>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "message": "Seat plans retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Bus A",
      "rows": 5,
      "cols": 4,
      "layout_type": "2-2",
      "created_at": "2025-07-08T10:00:00",
      "updated_at": "2025-07-08T10:00:00"
    },
    {
      "id": 2,
      "name": "Train B",
      "rows": 10,
      "cols": 3,
      "layout_type": "3-3",
      "created_at": "2025-07-08T11:00:00",
      "updated_at": "2025-07-08T11:00:00"
    }
  ]
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
