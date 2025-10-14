@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Popular Route Position</h1>

        <h3>Request</h3>
        <p>Update a specific route by ID:</p>
        <pre><code>PATCH /routes/update-popular-positions</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
    {
        "popular_positions": [
            {
                "route_id": 1,
                "popular_position": 1
            },
            {
                "route_id": 2,
                "popular_position": 2
            }
        ],
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
  "message": "Popular route position updated successfully",
  "data": {}
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
