@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Multi-Seat Booking Workflow</h1>

        <div class="alert alert-info">
            <h5><i class="fas fa-users"></i> Multi-Seat Booking</h5>
            <p>This guide shows how to book multiple seats (A1, A2, A3) using the seat request system. All seats will be grouped under one issue ID and expire simultaneously.</p>
        </div>

        <h3>Scenario: User wants to book 3 seats (A1, A2, A3)</h3>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6><i class="fas fa-play"></i> Step 1: First Seat</h6>
                    </div>
                    <div class="card-body">
                        <p>Creates new issue</p>
                        <code>seat_inventory_id: 123</code>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-plus"></i> Step 2: Second Seat</h6>
                    </div>
                    <div class="card-body">
                        <p>Add to existing issue</p>
                        <code>seat_inventory_id: 124</code>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-check"></i> Step 3: Third Seat</h6>
                    </div>
                    <div class="card-body">
                        <p>Complete the group</p>
                        <code>seat_inventory_id: 125</code>
                    </div>
                </div>
            </div>
        </div>

        <h3>1. First Seat Request (Creates New Issue)</h3>

        <h4>Request:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>POST /seat-requests

{
  "seat_inventory_id": 123,
  "notes": "Window seat preferred"
}</code></pre>
            </div>
        </div>

        <h4>Response:</h4>
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

        <h3>2. Second Seat Request (Add to Existing Issue)</h3>

        <h4>Request:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>POST /seat-requests

{
  "seat_inventory_id": 124,
  "issue_id": "SR-20250821-143052-A1B2C3",
  "notes": "Adjacent to A1"
}</code></pre>
            </div>
        </div>

        <h4>Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 201,
  "message": "Seat blocked successfully for 5 minutes",
  "data": {
    "seat_request_id": 457,
    "issue_id": "SR-20250821-143052-A1B2C3",
    "seat_inventory_id": 124,
    "status": "pending",
    "blocked_until": "2025-08-21T14:35:52.000000Z",
    "issue_summary": {
      "issue_id": "SR-20250821-143052-A1B2C3",
      "total_seats_in_issue": 2,
      "seats": [
        {
          "seat_request_id": 456,
          "seat_inventory_id": 123,
          "seat_id": 45,
          "status": "pending",
          "seat_number": "A1"
        },
        {
          "seat_request_id": 457,
          "seat_inventory_id": 124,
          "seat_id": 46,
          "status": "pending",
          "seat_number": "A2"
        }
      ]
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h3>3. Third Seat Request (Complete the Group)</h3>

        <h4>Request:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>POST /seat-requests

{
  "seat_inventory_id": 125,
  "issue_id": "SR-20250821-143052-A1B2C3",
  "notes": "Complete the row A1-A2-A3"
}</code></pre>
            </div>
        </div>

        <h4>Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 201,
  "message": "Seat blocked successfully for 5 minutes",
  "data": {
    "seat_request_id": 458,
    "issue_id": "SR-20250821-143052-A1B2C3",
    "seat_inventory_id": 125,
    "status": "pending",
    "blocked_until": "2025-08-21T14:35:52.000000Z",
    "issue_summary": {
      "issue_id": "SR-20250821-143052-A1B2C3",
      "total_seats_in_issue": 3,
      "seats": [
        {
          "seat_request_id": 456,
          "seat_inventory_id": 123,
          "seat_id": 45,
          "status": "pending",
          "seat_number": "A1"
        },
        {
          "seat_request_id": 457,
          "seat_inventory_id": 124,
          "seat_id": 46,
          "status": "pending",
          "seat_number": "A2"
        },
        {
          "seat_request_id": 458,
          "seat_inventory_id": 125,
          "seat_id": 47,
          "status": "pending",
          "seat_number": "A3"
        }
      ]
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h3>JavaScript Implementation Example</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
class SeatRequestManager {
  constructor(authToken) {
    this.authToken = authToken;
    this.baseURL = '/api/seat-requests';
    this.currentIssueId = null;
  }

  async requestSeat(seatInventoryId, notes = '') {
    const payload = {
      seat_inventory_id: seatInventoryId,
      notes: notes
    };

    // If we have an existing issue, add to it
    if (this.currentIssueId) {
      payload.issue_id = this.currentIssueId;
    }

    const response = await fetch(this.baseURL, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.authToken}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (response.ok) {
      // Store issue ID for subsequent requests
      this.currentIssueId = data.data.issue_id;
      return data;
    } else {
      throw new Error(data.message);
    }
  }

  async getAllSeatsInIssue() {
    if (!this.currentIssueId) {
      throw new Error('No active issue');
    }

    const response = await fetch(`${this.baseURL}/${this.currentIssueId}`, {
      headers: {
        'Authorization': `Bearer ${this.authToken}`
      }
    });

    return await response.json();
  }

  async cancelEntireIssue() {
    if (!this.currentIssueId) {
      throw new Error('No active issue');
    }

    const response = await fetch(`${this.baseURL}/${this.currentIssueId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${this.authToken}`
      }
    });

    const data = await response.json();

    if (response.ok) {
      this.currentIssueId = null; // Reset issue ID
    }

    return data;
  }
}

// Usage Example
async function bookThreeSeats() {
  const manager = new SeatRequestManager('your_auth_token');

  try {
    // Request first seat (creates new issue)
    console.log('Requesting seat A1...');
    const seat1 = await manager.requestSeat(123, 'Window seat preferred');
    console.log('Seat A1 blocked:', seat1.data.issue_id);

    // Request second seat (adds to existing issue)
    console.log('Requesting seat A2...');
    const seat2 = await manager.requestSeat(124, 'Adjacent to A1');
    console.log('Seat A2 blocked, total seats:', seat2.data.issue_summary.total_seats_in_issue);

    // Request third seat (adds to existing issue)
    console.log('Requesting seat A3...');
    const seat3 = await manager.requestSeat(125, 'Complete the row A1-A2-A3');
    console.log('Seat A3 blocked, total seats:', seat3.data.issue_summary.total_seats_in_issue);

    // Check all seats status
    console.log('Checking all seats status...');
    const status = await manager.getAllSeatsInIssue();
    console.log('All seats:', status.data.seats.map(s => s.seat_info.seat.seat_number));

    return status;
  } catch (error) {
    console.error('Error booking seats:', error.message);
    throw error;
  }
}

// Execute the booking
bookThreeSeats()
  .then(result => console.log('Successfully blocked all seats!', result))
  .catch(error => console.error('Failed to block seats:', error));
                </code></pre>
            </div>
        </div>

        <h3>Key Points</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-clock"></i> Timing</h6>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>All seats have same 5-minute expiry</li>
                            <li>Timer resets with each new seat added</li>
                            <li>Synchronized expiration for all seats</li>
                            <li>Automatic release after timeout</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-cog"></i> Management</h6>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>First request creates new issue</li>
                            <li>Subsequent requests add to issue</li>
                            <li>Independent seat blocking</li>
                            <li>Flexible cancellation options</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h3>Best Practices</h3>
        <ul>
            <li><strong>Store Issue ID:</strong> Keep the issue_id from the first request to add more seats</li>
            <li><strong>Monitor Timer:</strong> Check remaining time before it expires</li>
            <li><strong>Error Handling:</strong> Handle seat unavailability gracefully</li>
            <li><strong>User Feedback:</strong> Show users which seats are selected and time remaining</li>
            <li><strong>Cleanup:</strong> Cancel unused requests to free seats for others</li>
        </ul>

        <h3>Common Use Cases</h3>
        <ul>
            <li><strong>Family Booking:</strong> Parents booking seats for family members</li>
            <li><strong>Group Travel:</strong> Booking adjacent seats for friends</li>
            <li><strong>Corporate Travel:</strong> Reserving seats for business team</li>
            <li><strong>Event Booking:</strong> Securing multiple seats for special occasions</li>
        </ul>
    </div>
@endsection
