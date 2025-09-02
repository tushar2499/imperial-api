@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Cancel All Seats from Issue</h1>

        <h3>Request</h3>
        <p>Cancel all pending seat requests from a specific issue with a <strong>DELETE</strong> request:</p>
        <pre><code>POST /seat-requests/cancel-issue</code></pre>

        <h4>Headers:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
Content-Type: application/json
Authorization: Bearer {your_access_token}
                </code></pre>
            </div>
        </div>

        <h4>Request Body:</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "issue_id": "ISSUE_20250828_001"
}
                </code></pre>
            </div>
        </div>

        <h4>Success Response (200 OK):</h4>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "All seats cancelled from issue successfully",
  "data": {
    "issue_id": "ISSUE_20250828_001",
    "cancelled_seats_count": 3,
    "cancelled_seats": [
      {
        "seat_request_id": 123,
        "seat_inventory_id": 456,
        "seat_id": 101,
        "status": "cancelled"
      },
      {
        "seat_request_id": 124,
        "seat_inventory_id": 457,
        "seat_id": 102,
        "status": "cancelled"
      },
      {
        "seat_request_id": 125,
        "seat_inventory_id": 458,
        "seat_id": 103,
        "status": "cancelled"
      }
    ],
    "user_id": 25,
    "cancelled_at": "2025-08-28T10:30:15.000000Z"
  }
}
                </code></pre>
            </div>
        </div>

        <h3>Request Parameters:</h3>
        <ul>
            <li><strong>issue_id</strong> - Required, the issue ID containing all seat requests to cancel</li>
        </ul>

        <h3>Response Fields:</h3>
        <ul>
            <li><strong>issue_id</strong> - The cancelled issue identifier</li>
            <li><strong>cancelled_seats_count</strong> - Number of seats successfully cancelled</li>
            <li><strong>cancelled_seats</strong> - Array of all cancelled seat requests</li>
            <li><strong>user_id</strong> - ID of the user who cancelled the issue</li>
            <li><strong>cancelled_at</strong> - Timestamp when cancellation occurred</li>
        </ul>

        <h3>Key Features:</h3>
        <ul>
            <li><strong>Bulk Cancellation</strong> - Cancel all seats in an issue with one request</li>
            <li><strong>Atomic Operation</strong> - All seats are cancelled together or not at all</li>
            <li><strong>Immediate Release</strong> - All cancelled seats become available instantly</li>
            <li><strong>Complete Cleanup</strong> - Entire issue is cleared from the system</li>
        </ul>

        <h3>Error Responses:</h3>

        <div class="card">
            <div class="card-header">
                <strong>404 Not Found - No Pending Requests</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 404,
  "message": "No pending seat requests found for this issue",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>422 Validation Error - Invalid Data</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 422,
  "message": "The given data was invalid.",
  "data": {
    "issue_id": ["The issue id field is required."]
  }
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>500 Server Error - Database Error</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 500,
  "message": "Failed to cancel seat request: Database connection error",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>JavaScript Example:</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
async function cancelAllSeatsFromIssue(issueId) {
  try {
    const response = await fetch('/api/seat-requests/cancel-issue', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      },
      body: JSON.stringify({
        issue_id: issueId
      })
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message);
    }

    const data = await response.json();

    console.log('Issue cancelled successfully:');
    console.log(`- Issue ID: ${data.data.issue_id}`);
    console.log(`- Cancelled seats: ${data.data.cancelled_seats_count}`);

    // Log each cancelled seat
    data.data.cancelled_seats.forEach(seat => {
      console.log(`  - Seat request ${seat.seat_request_id}: cancelled`);
    });

    // Clear the UI
    clearSeatSelection();
    resetIssueState();

    return data;
  } catch (error) {
    console.error('Error cancelling issue:', error);
    showErrorMessage(`Failed to cancel all seats: ${error.message}`);
    throw error;
  }
}

// Usage example with confirmation
async function cancelIssueWithConfirmation(issueId, seatCount) {
  const message = `Are you sure you want to cancel all ${seatCount} seat(s) from issue ${issueId}?`;
  const confirm = window.confirm(message);

  if (confirm) {
    try {
      const result = await cancelAllSeatsFromIssue(issueId);

      showSuccessMessage(
        `Successfully cancelled ${result.data.cancelled_seats_count} seats from issue ${issueId}`
      );

      // Redirect to seat selection or trip search
      redirectToSeatSelection();

      return result;
    } catch (error) {
      // Error handling is done in cancelAllSeatsFromIssue
    }
  } else {
    console.log('Issue cancellation aborted by user');
  }
}

// Enhanced usage with pre-flight check
async function safeCancelIssue(issueId) {
  try {
    // First check current status
    const statusResponse = await fetch(`/api/seat-requests/${issueId}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      }
    });

    if (!statusResponse.ok) {
      throw new Error('Could not check issue status');
    }

    const statusData = await statusResponse.json();
    const pendingSeats = statusData.data.seats.filter(s => s.status === 'pending');

    if (pendingSeats.length === 0) {
      showInfoMessage('No pending seats to cancel in this issue');
      return null;
    }

    // Show detailed confirmation
    const seatNumbers = pendingSeats.map(s => s.seat_info.seat.seat_number).join(', ');
    const confirmMessage = `Cancel ${pendingSeats.length} pending seats (${seatNumbers}) from issue ${issueId}?`;

    if (window.confirm(confirmMessage)) {
      const result = await cancelAllSeatsFromIssue(issueId);

      showSuccessMessage(
        `Successfully cancelled seats: ${seatNumbers}`
      );

      return result;
    }

    return null;
  } catch (error) {
    showErrorMessage(`Error during cancellation: ${error.message}`);
    throw error;
  }
}

// Batch operations helper
async function cancelMultipleIssues(issueIds) {
  const results = [];
  const errors = [];

  for (const issueId of issueIds) {
    try {
      const result = await cancelAllSeatsFromIssue(issueId);
      results.push(result);
    } catch (error) {
      errors.push({ issueId, error: error.message });
    }
  }

  // Report results
  if (results.length > 0) {
    const totalCancelled = results.reduce((sum, r) => sum + r.data.cancelled_seats_count, 0);
    showSuccessMessage(`Successfully cancelled ${totalCancelled} seats from ${results.length} issues`);
  }

  if (errors.length > 0) {
    showErrorMessage(`Failed to cancel ${errors.length} issues`);
    console.error('Cancellation errors:', errors);
  }

  return { results, errors };
}

// Helper functions
function clearSeatSelection() {
  // Clear all selected seats from UI
  document.querySelectorAll('.seat.selected').forEach(seat => {
    seat.classList.remove('selected');
    seat.classList.add('available');
  });
}

function resetIssueState() {
  // Reset issue-related UI state
  const issueInfoElement = document.getElementById('issue-info');
  if (issueInfoElement) {
    issueInfoElement.innerHTML = '<p>No seats selected</p>';
  }

  // Clear stored issue ID
  sessionStorage.removeItem('current_issue_id');
}

function showSuccessMessage(message) {
  // Your success notification implementation
  console.log('[SUCCESS]', message);
}

function showErrorMessage(message) {
  // Your error notification implementation
  console.log('[ERROR]', message);
}

function showInfoMessage(message) {
  // Your info notification implementation
  console.log('[INFO]', message);
}

function redirectToSeatSelection() {
  // Redirect back to seat selection
  window.location.href = '/select-seats';
}
                </code></pre>
            </div>
        </div>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Complete Cancellation</strong> - User decides not to proceed with any seat selection</li>
            <li><strong>Start Over</strong> - User wants to start fresh with seat selection</li>
            <li><strong>Timeout Prevention</strong> - Cancel all seats before they expire automatically</li>
            <li><strong>Session Cleanup</strong> - Clear all selections when user leaves booking flow</li>
            <li><strong>Group Booking Abort</strong> - Cancel entire group reservation</li>
        </ul>

        <h3>Best Practices:</h3>
        <ul>
            <li><strong>Confirmation</strong> - Always confirm before cancelling multiple seats</li>
            <li><strong>Status Check</strong> - Verify issue status before attempting cancellation</li>
            <li><strong>Clear Feedback</strong> - Show exactly which seats were cancelled</li>
            <li><strong>UI Reset</strong> - Clear all seat selection UI after successful cancellation</li>
            <li><strong>Error Recovery</strong> - Handle partial failures gracefully</li>
            <li><strong>Audit Trail</strong> - Log bulk cancellations for tracking purposes</li>
        </ul>

        <h3>Comparison with Individual Cancellation:</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Individual Cancellation</th>
                        <th>Issue Cancellation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Endpoint</td>
                        <td>/seat-requests/cancel</td>
                        <td>/seat-requests/cancel-issue</td>
                    </tr>
                    <tr>
                        <td>Scope</td>
                        <td>Single seat request</td>
                        <td>All seats in issue</td>
                    </tr>
                    <tr>
                        <td>Parameters</td>
                        <td>seat_inventory_id + issue_id</td>
                        <td>issue_id only</td>
                    </tr>
                    <tr>
                        <td>Use Case</td>
                        <td>Selective cancellation</td>
                        <td>Complete cancellation</td>
                    </tr>
                    <tr>
                        <td>Issue Impact</td>
                        <td>Issue continues with remaining seats</td>
                        <td>Entire issue is cleared</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3>Notes:</h3>
        <ul>
            <li>Only cancels pending seat requests - already booked seats are not affected</li>
            <li>Operation is atomic - all seats are cancelled together or none at all</li>
            <li>All cancelled seats become immediately available for other users</li>
            <li>The issue ID becomes invalid after successful cancellation</li>
            <li>Cannot be undone - user must create new seat requests if needed</li>
        </ul>
    </div>
@endsection
