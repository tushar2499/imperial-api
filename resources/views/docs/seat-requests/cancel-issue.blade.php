@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Cancel Individual Seat Request</h1>

        <h3>Request</h3>
        <p>Cancel a specific seat request while keeping other seats in the same issue with a <strong>DELETE</strong> request:</p>
        <pre><code>DELETE /seat-requests/cancel</code></pre>

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
  "seat_inventory_id": 456,
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
  "message": "Seat request cancelled successfully",
  "data": {
    "cancelled_seat_request_id": 123,
    "seat_inventory_id": 456,
    "issue_id": "ISSUE_20250828_001",
    "trip_id": 789,
    "seat_info": {
      "seat_id": 101,
      "seat_number": "A1",
      "row_position": 1,
      "col_position": 1,
      "seat_type": "window"
    },
    "seat_status": "available",
    "request_status": "cancelled",
    "blocked_until": null,
    "user_id": 25,
    "cancelled_at": "2025-08-28T10:30:15.000000Z",
    "remaining_seats_in_issue": {
      "issue_id": "ISSUE_20250828_001",
      "total_remaining_seats": 2,
      "seats": [
        {
          "seat_request_id": 124,
          "seat_inventory_id": 457,
          "seat_id": 102,
          "status": "pending",
          "blocked_until": "2025-08-28T10:35:00.000000Z"
        },
        {
          "seat_request_id": 125,
          "seat_inventory_id": 458,
          "seat_id": 103,
          "status": "pending",
          "blocked_until": "2025-08-28T10:35:00.000000Z"
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
            <li><strong>seat_inventory_id</strong> - Required, the specific seat inventory ID to cancel</li>
            <li><strong>issue_id</strong> - Required, the issue ID containing the seat request</li>
        </ul>

        <h3>Response Fields:</h3>
        <ul>
            <li><strong>cancelled_seat_request_id</strong> - ID of the cancelled seat request</li>
            <li><strong>seat_info</strong> - Details of the cancelled seat</li>
            <li><strong>seat_status</strong> - New status of the seat (available)</li>
            <li><strong>request_status</strong> - Status of the request (cancelled)</li>
            <li><strong>remaining_seats_in_issue</strong> - Other seats still in the issue</li>
        </ul>

        <h3>Key Features:</h3>
        <ul>
            <li><strong>Selective Cancellation</strong> - Cancel only one seat from a multi-seat issue</li>
            <li><strong>Issue Preservation</strong> - Other seats in the issue remain active</li>
            <li><strong>Immediate Release</strong> - Cancelled seat becomes available instantly</li>
            <li><strong>Remaining Seats Info</strong> - Response shows what seats are still in the issue</li>
        </ul>

        <h3>Error Responses:</h3>

        <div class="card">
            <div class="card-header">
                <strong>404 Not Found - Seat Request Not Found</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 404,
  "message": "Seat request not found, already cancelled, or you do not have permission to remove it",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>403 Forbidden - No Permission</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 403,
  "message": "You do not have permission to remove this seat request",
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
    "seat_inventory_id": ["The seat inventory id field is required."],
    "issue_id": ["The issue id field is required."]
  }
}
                </code></pre>
            </div>
        </div>

        <h3>JavaScript Example:</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
async function cancelIndividualSeat(seatInventoryId, issueId) {
  try {
    const response = await fetch('/api/seat-requests/cancel', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      },
      body: JSON.stringify({
        seat_inventory_id: seatInventoryId,
        issue_id: issueId
      })
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message);
    }

    const data = await response.json();

    console.log('Seat cancelled successfully:');
    console.log(`- Cancelled seat: ${data.data.seat_info.seat_number}`);
    console.log(`- Remaining seats in issue: ${data.data.remaining_seats_in_issue.total_remaining_seats}`);

    // Update UI to reflect cancellation
    updateSeatUI(data.data.seat_inventory_id, 'available');

    // Update issue summary
    updateIssueSummary(data.data.remaining_seats_in_issue);

    return data;
  } catch (error) {
    console.error('Error cancelling seat:', error);
    showErrorMessage(`Failed to cancel seat: ${error.message}`);
    throw error;
  }
}

// Usage example with confirmation
async function cancelSeatWithConfirmation(seatNumber, seatInventoryId, issueId) {
  const confirm = window.confirm(`Are you sure you want to cancel seat ${seatNumber}?`);

  if (confirm) {
    try {
      const result = await cancelIndividualSeat(seatInventoryId, issueId);

      showSuccessMessage(`Seat ${seatNumber} cancelled successfully`);

      // Check if this was the last seat in the issue
      if (result.data.remaining_seats_in_issue.total_remaining_seats === 0) {
        showInfoMessage('All seats have been cancelled from this issue');
        redirectToSeatSelection();
      }

      return result;
    } catch (error) {
      // Error handling is done in cancelIndividualSeat
    }
  } else {
    console.log('Seat cancellation aborted by user');
  }
}

// Helper functions
function updateSeatUI(seatInventoryId, status) {
  const seatElement = document.querySelector(`[data-seat-inventory-id="${seatInventoryId}"]`);
  if (seatElement) {
    seatElement.classList.remove('selected', 'blocked');
    seatElement.classList.add(status);
  }
}

function updateIssueSummary(remainingSeats) {
  const summaryElement = document.getElementById('issue-summary');
  if (summaryElement) {
    summaryElement.innerHTML = `
      <h5>Issue: ${remainingSeats.issue_id}</h5>
      <p>Remaining seats: ${remainingSeats.total_remaining_seats}</p>
    `;
  }
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
  // Redirect to seat selection page
  window.location.href = '/select-seats';
}
                </code></pre>
            </div>
        </div>

        <h3>Use Cases:</h3>
        <ul>
            <li><strong>Partial Cancellation</strong> - User wants to keep some seats but cancel others</li>
            <li><strong>Seat Adjustment</strong> - Cancel one seat to select a different one</li>
            <li><strong>Group Changes</strong> - Remove one person from a group booking</li>
            <li><strong>Error Correction</strong> - Cancel accidentally selected seats</li>
        </ul>

        <h3>Best Practices:</h3>
        <ul>
            <li><strong>Confirmation</strong> - Always confirm with user before cancelling individual seats</li>
            <li><strong>UI Updates</strong> - Immediately update seat selection UI after cancellation</li>
            <li><strong>Issue Tracking</strong> - Keep track of remaining seats in the issue</li>
            <li><strong>Error Handling</strong> - Handle permission and validation errors gracefully</li>
            <li><strong>User Feedback</strong> - Provide clear feedback about the cancellation result</li>
        </ul>

        <h3>Notes:</h3>
        <ul>
            <li>Only the user who created the seat request can cancel it</li>
            <li>Cancelled seats become immediately available for other users</li>
            <li>If this is the last seat in an issue, the issue remains but becomes empty</li>
            <li>The operation is atomic - either succeeds completely or fails without changes</li>
        </ul>
    </div>
@endsection
