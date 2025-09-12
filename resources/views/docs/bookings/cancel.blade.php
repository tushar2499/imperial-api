@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Cancel Booking</h1>

        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Important Notice</h5>
            <p>Based on the BookingController provided, there is no dedicated cancel endpoint implemented. Booking cancellation is typically handled through the update endpoint by modifying seat statuses or booking types.</p>
        </div>

        <h3>Alternative Cancellation Methods</h3>

        <h4>Method 1: Update Booking with Cancel Status</h4>
        <p>Use the update endpoint to mark seats as cancelled:</p>
        <pre><code>PUT /bookings/{id}</code></pre>

        <h5>Request Body for Cancellation:</h5>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "mobile": "01712345678",
  "name": "John Doe",
  "gender": "Male",
  "age": 30,
  "address": "123 Main Street, Dhaka",
  "passport_no": null,
  "nid": "1234567890123",
  "nationality": "Bangladeshi",
  "email": "john@example.com",
  "trip_id": 1,
  "type": 5,
  "date": "2025-08-18",
  "time": "10:00:00",
  "route_id": 1,
  "boarding_id": 1,
  "dropping_id": 2,
  "booking_details": [
    {
      "seat_inventory_id": 1,
      "seat_id": 1,
      "price": 1500,
      "discount": 50,
      "cancel_status": 1
    },
    {
      "seat_inventory_id": 2,
      "seat_id": 2,
      "price": 1500,
      "discount": 0,
      "cancel_status": 1
    }
  ]
}
                </code></pre>
            </div>
        </div>

        <h5>Sample Response:</h5>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "status": "success",
  "code": 200,
  "message": "Booking updated successfully",
  "data": {
    "booking": {
      "id": 5,
      "pnr_number": "L2DB92GFBG",
      "trip_id": 1,
      "type": 5,
      "date": "2025-08-18",
      "time": "10:00:00",
      "route_id": 1,
      "boarding_id": 1,
      "dropping_id": 2,
      "total_tickets": 0,
      "total_price": 0,
      "total_discount": 0,
      "total_amount": 0,
      "status": "updated",
      "updated_at": "2025-08-18T11:30:00.000000Z"
    },
    "customer": {
      "id": 1,
      "name": "John Doe",
      "mobile": "01712345678",
      "email": "john@example.com",
      "gender": "Male",
      "age": 30,
      "address": "123 Main Street, Dhaka",
      "passport_no": null,
      "nid": "1234567890123",
      "nationality": "Bangladeshi"
    },
    "boarding_info": {
      "id": 1,
      "trip_id": 1,
      "time": "08:00:00",
      "counter_id": 1,
      "counter_name": "Kallyanpur Counter",
      "counter_location": "Near Kallyanpur Bus Stand",
      "district_name": "Dhaka"
    },
    "dropping_info": {
      "id": 2,
      "trip_id": 1,
      "time": "14:00:00",
      "counter_id": 2,
      "counter_name": "GEC Counter",
      "counter_location": "GEC Circle",
      "district_name": "Chittagong"
    },
    "updated_seats": [],
    "seat_status_summary": {
      "total_seats_updated": 0,
      "seat_status": "cancelled",
      "payment_status": "refund_pending"
    }
  }
}
                </code></pre>
            </div>
        </div>

        <h4>Method 2: Seat Inventory Release</h4>
        <p>The update process automatically:</p>
        <ul>
            <li>Releases previously booked seats back to available status</li>
            <li>Sets booking_id to null in seat inventory</li>
            <li>Clears blocked_until timestamps</li>
            <li>Updates seat status to STATUS_AVAILABLE</li>
        </ul>

        <h3>Cancellation Logic in Update Method</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Seat Release Process</h6>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Get existing booking details</li>
                            <li>Release previously booked seats</li>
                            <li>Set seat status to available</li>
                            <li>Clear booking references</li>
                            <li>Delete old booking details</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Cancel Status Handling</h6>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><strong>cancel_status: 0</strong> - Keep seat</li>
                            <li><strong>cancel_status: 1</strong> - Cancel seat</li>
                            <li>Only seats with cancel_status = 0 are processed</li>
                            <li>Cancelled seats are excluded from new booking</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h3>Recommended Implementation</h3>
        <p>For a proper cancellation API, consider implementing a dedicated cancel endpoint:</p>

        <h5>Proposed Cancel Endpoint:</h5>
        <div class="card">
            <div class="card-body">
                <pre><code>DELETE /bookings/{id}/cancel</code></pre>
            </div>
        </div>

        <h5>Proposed Request Body:</h5>
        <div class="card">
            <div class="card-body">
                <pre><code>
{
  "cancellation_reason": "Customer requested cancellation",
  "refund_amount": 2950,
  "cancellation_fee": 100,
  "net_refund": 2850
}
                </code></pre>
            </div>
        </div>

        <h3>Current Workaround Process</h3>
        <ol>
            <li><strong>Step 1:</strong> Get booking details using GET /bookings/{id}</li>
            <li><strong>Step 2:</strong> Prepare update request with cancel_status = 1 for all seats</li>
            <li><strong>Step 3:</strong> Send PUT /bookings/{id} with empty booking_details or cancelled seats</li>
            <li><strong>Step 4:</strong> Verify seats are released back to available status</li>
        </ol>

        <h3>Important Notes:</h3>
        <ul>
            <li><strong>No Dedicated Cancel Endpoint</strong> - Use update method with cancel_status</li>
            <li><strong>Seat Release</strong> - Update method automatically releases previously booked seats</li>
            <li><strong>Partial Cancellation</strong> - Can cancel individual seats using cancel_status field</li>
            <li><strong>Complete Cancellation</strong> - Set cancel_status = 1 for all seats or use empty booking_details</li>
            <li><strong>Refund Handling</strong> - Not implemented in current controller, needs separate logic</li>
        </ul>

        <h3>Error Handling:</h3>
        <div class="card">
            <div class="card-header">
                <strong>404 Not Found - Booking Not Found</strong>
            </div>
            <div class="card-body">
                <pre><code>
{
  "status": "error",
  "code": 404,
  "message": "Booking not found",
  "data": null
}
                </code></pre>
            </div>
        </div>

        <h3>JavaScript Example:</h3>
        <div class="card">
            <div class="card-body">
                <pre><code>
async function cancelBooking(bookingId) {
  try {
    // First get the current booking details
    const bookingResponse = await fetch(`/api/bookings/${bookingId}`, {
      headers: {
        'Authorization': 'Bearer your_token'
      }
    });

    if (!bookingResponse.ok) {
      throw new Error('Booking not found');
    }

    const booking = await bookingResponse.json();

    // Prepare cancellation request
    const cancelRequest = {
      ...booking.data.customer,
      trip_id: booking.data.booking.trip_id,
      type: 5, // Cancelled status
      date: booking.data.booking.date,
      time: booking.data.booking.time,
      route_id: booking.data.booking.route_id,
      boarding_id: booking.data.booking.boarding_id,
      dropping_id: booking.data.booking.dropping_id,
      booking_details: booking.data.booked_seats.map(seat => ({
        seat_inventory_id: seat.seat_inventory_id,
        seat_id: seat.seat_id,
        price: seat.price,
        discount: seat.discount,
        cancel_status: 1 // Cancel this seat
      }))
    };

    // Send update request for cancellation
    const cancelResponse = await fetch(`/api/bookings/${bookingId}`, {
      method: 'PUT',
      headers: {
        'Authorization': 'Bearer your_token',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(cancelRequest)
    });

    if (!cancelResponse.ok) {
      throw new Error('Failed to cancel booking');
    }

    const result = await cancelResponse.json();
    console.log('Booking cancelled successfully:', result);

    return result;
  } catch (error) {
    console.error('Error cancelling booking:', error);
    throw error;
  }
}

// Usage
cancelBooking(5)
  .then(result => {
    console.log('Cancellation completed:', result.data.seat_status_summary);
  })
  .catch(error => {
    console.error('Booking cancellation failed:', error.message);
  });
                </code></pre>
            </div>
        </div>
    </div>
@endsection
