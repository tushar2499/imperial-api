@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Seat Request System Overview</h1>

        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Seat Request System</h5>
            <p>The Seat Request system allows users to temporarily block seats before booking. This prevents conflicts when multiple users try to book the same seat simultaneously.</p>
        </div>

        <h3>How It Works</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-lock"></i> Seat Blocking</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>User requests a seat</li>
                            <li>Seat is blocked for 5 minutes</li>
                            <li>Other users cannot book blocked seats</li>
                            <li>User has time to complete booking</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-users"></i> Multi-Seat Support</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>Multiple seats can be grouped in an "issue"</li>
                            <li>All seats in an issue have the same expiry time</li>
                            <li>Can add more seats to existing issue</li>
                            <li>Cancel individual seats or entire issue</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h3>Key Concepts</h3>

        <h4>Issue ID</h4>
        <p>An <strong>Issue ID</strong> is a unique identifier that groups multiple seat requests together. Format: <code>SR-YYYYMMDD-HHMMSS-RANDOM</code></p>
        <div class="card">
            <div class="card-body">
                <pre><code>Example: SR-20250821-143052-A1B2C3</code></pre>
            </div>
        </div>

        <h4>Seat Request States</h4>
        <ul>
            <li><strong>pending</strong> - Seat is blocked and waiting for booking</li>
            <li><strong>expired</strong> - 5-minute timer expired, seat released</li>
            <li><strong>cancelled</strong> - User cancelled the request</li>
            <li><strong>booked</strong> - Seat has been successfully booked</li>
        </ul>

        <h4>Timing</h4>
        <ul>
            <li><strong>Block Duration:</strong> 5 minutes from request time</li>
            <li><strong>Auto-Release:</strong> Seats automatically released after expiry</li>
            <li><strong>Remaining Time:</strong> API provides countdown information</li>
        </ul>

        <h3>API Endpoints</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Endpoint</th>
                        <th>Description</th>
                        <th>Documentation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge bg-success">POST</span></td>
                        <td>/seat-requests</td>
                        <td>Create a seat request (block seat)</td>
                        <td><a href="{{ url('/docs/seat-requests/create') }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-info">GET</span></td>
                        <td>/seat-requests/{issue_id}</td>
                        <td>Check status of all seats in an issue</td>
                        <td><a href="{{ url('/docs/seat-requests/status') }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-danger">DELETE</span></td>
                        <td>/seat-requests/{issue_id}</td>
                        <td>Cancel entire issue (all seats)</td>
                        <td><a href="{{ url('/docs/seat-requests/cancel') }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3>Workflows</h3>

        <h4>Single Seat Booking</h4>
        <ol>
            <li>User searches for trips</li>
            <li>User selects a seat</li>
            <li>POST to <code>/seat-requests</code> (creates new issue)</li>
            <li>Seat is blocked for 5 minutes</li>
            <li>User proceeds to booking process</li>
        </ol>

        <h4>Multi-Seat Booking</h4>
        <ol>
            <li>User requests first seat (creates issue)</li>
            <li>User requests additional seats (adds to same issue)</li>
            <li>All seats have synchronized expiry time</li>
            <li>User can check status of all seats</li>
            <li>User proceeds to book all seats or cancels entire issue</li>
        </ol>

        <h3>Best Practices</h3>
        <ul>
            <li><strong>Check Remaining Time:</strong> Always monitor the remaining time to avoid expiry</li>
            <li><strong>Handle Expiry:</strong> Implement proper handling when seats expire</li>
            <li><strong>Issue Management:</strong> Use the same issue_id for related seat requests</li>
            <li><strong>Error Handling:</strong> Handle seat already blocked scenarios gracefully</li>
            <li><strong>Cleanup:</strong> Cancel unused seat requests to free up seats for other users</li>
        </ul>

        <h3>Quick Navigation</h3>
        <div class="row">
            <div class="col-md-3">
                <a href="{{ url('/docs/seat-requests/create') }}" class="btn btn-outline-primary btn-block mb-2">
                    <i class="fas fa-plus"></i> Create Request
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ url('/docs/seat-requests/status') }}" class="btn btn-outline-info btn-block mb-2">
                    <i class="fas fa-info-circle"></i> Check Status
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ url('/docs/seat-requests/multi-seat') }}" class="btn btn-outline-success btn-block mb-2">
                    <i class="fas fa-users"></i> Multi-Seat Guide
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ url('/docs/seat-requests/cancel') }}" class="btn btn-outline-danger btn-block mb-2">
                    <i class="fas fa-times"></i> Cancel Requests
                </a>
            </div>
        </div>
    </div>
@endsection
