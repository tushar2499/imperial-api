@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Create Seat Request</h1>

        <h3>Request</h3>
        <p>Create a seat request to temporarily block a seat with a <strong>POST</strong> request:</p>
        <pre><code>POST /seat-requests</code></pre>

        <h4>Request Body (New Issue):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "seat_inventory_id": 123,
  "notes": "Window seat preferred"
}
                </code></pre>
            </div>
        </div>

        <h4>Request Body (Add to Existing Issue):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "seat_inventory_id": 124,
  "issue_id": "SR-20250821-143052-A1B2C3",
  "notes": "Adjacent to A1"
}
                </code></pre>
            </div>
        </div>

        <h4>Sample Response (201 Created):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 201,
  "message": "Seat blocked successfully for 5 minutes",
  "data": {
    "seat_request_id": 456,
    "issue_id": "SR-20250821-143052-A1B2C3",
    "seat_inventory_id": 123,
    "status": "pending",
    "blocked_until": "2025-08-21T14:35:52.000000Z",
    "blocked_for_minutes": 5,
    "remaining_time": {
      "minutes": 5,
      "seconds": 300,
      "expires_at": "2025-08-21T14:35:52.000000Z"
    },
    "seat_info": {
      "seat": {
        "seat_number": "A1",
        "row_position": 1,
        "col_position": 1,
        "seat_type": "window"
      },
      "trip": {
        "trip_date": "2025-08-25",
        "coach_no": "101",
        "coach_type": 1,
        "coach_type_name": "AC"
      },
      "route": {
        "start_district": "Dhaka",
        "end_district": "Chittagong",
        "route_display": "Dhaka → Chittagong",
        "distance": 264,
        "duration": "06:00"
      }
    },
    "user_id": 42,
    "created_at": "2025-08-21T14:30:52.000000Z",
    "issue_summary": {
      "issue_id": "SR-20250821-143052-A1B2C3",
      "total_seats_in_issue": 1,
      "seats": [
        {
          "seat_request_id": 456,
          "seat_inventory_id": 123,
          "seat_id": 45,
          "status": "pending",
          "seat_number": "A1"
        }
      ]
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Request Parameters:</h3>
        <ul>
            <li><strong>seat_inventory_id</strong> - Required, the seat inventory ID to block</li>
            <li><strong>issue_id</strong> - Optional, existing issue ID to add this seat to</li>
            <li><strong>notes</strong> - Optional, user notes for this seat request</li>
        </ul>

        <h3>Response Fields:</h3>
        <ul>
            <li><strong>seat_request_id</strong> - Unique ID for this seat request</li>
            <li><strong>issue_id</strong> - Issue ID grouping related seat requests</li>
            <li><strong>status</strong> - Current status (pending, expired, cancelled, booked)</li>
            <li><strong>blocked_until</strong> - UTC timestamp when block expires</li>
            <li><strong>remaining_time</strong> - Countdown information</li>
            <li><strong>seat_info</strong> - Complete seat, trip, and route details</li>
            <li><strong>issue_summary</strong> - All seats in the current issue</li>
        </ul>

        <h3>Behavior:</h3>
        <ul>
            <li><strong>New Issue:</strong> If no issue_id provided, creates a new issue</li>
            <li><strong>Add to Issue:</strong> If issue_id provided, adds seat to existing issue</li>
            <li><strong>Synchronized Expiry:</strong> All seats in an issue expire at the same time</li>
            <li><strong>5-Minute Block:</strong> Seats are blocked for exactly 5 minutes</li>
        </ul>

        <h3>Error Responses:</h3>

        <div class="card">
            <div class="card-header">
                <strong>400 Bad Request - Seat Already Blocked</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 400,
  "message": "Seat is already blocked by another user",
  "data": {
    "seat_inventory_id": 123,
    "blocked_until": "2025-08-21T14:35:52.000000Z",
    "blocked_by_user": 41
  }
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>404 Not Found - Seat Not Available</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 404,
  "message": "Seat inventory not found or not available",
  "data": {
    "seat_inventory_id": 123,
    "reason": "already_booked"
  }
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>400 Bad Request - Invalid Issue ID</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 400,
  "message": "Issue ID not found or not owned by current user",
  "data": {
    "issue_id": "SR-20250821-143052-A1B2C3"
  }
}
                </code></pre>
            </div>
        </div>

        <h3>JavaScript Example:</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
// Request first seat (creates new issue)
const response1 = await fetch('/api/seat-requests', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer your_token',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    seat_inventory_id: 123,
    notes: 'Window seat preferred'
  })
});

const data1 = await response1.json();
const issueId = data1.data.issue_id;

// Request second seat (add to existing issue)
const response2 = await fetch('/api/seat-requests', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer your_token',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    seat_inventory_id: 124,
    issue_id: issueId,
    notes: 'Adjacent seat'
  })
});

const data2 = await response2.json();
console.log('Total seats in issue:', data2.data.issue_summary.total_seats_in_issue);
                </code></pre>
            </div>
        </div>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Single Seat Booking:</strong> User selects one seat and proceeds to payment</li>
            <li><strong>Family Booking:</strong> Book multiple adjacent seats for family members</li>
            <li><strong>Group Booking:</strong> Reserve multiple seats before confirming the group</li>
            <li><strong>Seat Selection:</strong> Allow users to "try" different seats before deciding</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>Each user can only block a limited number of seats simultaneously</li>
            <li>Blocked seats are automatically released after 5 minutes</li>
            <li>Users cannot block seats that are already booked or blocked by others</li>
            <li>The same user can have multiple issues active at the same time</li>
        </ul>
    </div>
@endsection
