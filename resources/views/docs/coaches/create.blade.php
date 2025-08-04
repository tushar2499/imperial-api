@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create a New Coach</h1>

        <h3>Request</h3>
        <p>Create a new coach with a <strong>POST</strong> request:</p>
        <pre><code>POST /coaches</code></pre>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "coach_no": "C002",
  "seat_plan_id": 2,
  "coach_type": 2,
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
  "message": "Coach created successfully",
  "data": {
    "id": 2,
    "coach_no": "C002",
    "seat_plan_id": 2,
    "coach_type": 2,
    "status": "active",
    "created_by": 2,
    "updated_by": 2,
    "created_at": "2021-02-15T12:00:00",
    "updated_at": "2021-02-15T12:00:00",
    "deleted_at": null
  }
}
                </code></pre>
            </div>
        </div>
    </div>
@endsection
