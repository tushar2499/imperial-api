@extends('layouts.app')

@section('content')
    <h1 class="mb-4">API Endpoints</h1>
    <div class="card">
        <div class="card-header">
            Available Endpoints
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>/api/v1/users</code></td>
                        <td>Get a list of all users</td>
                    </tr>
                    <tr>
                        <td><code>/api/v1/posts</code></td>
                        <td>Retrieve all posts</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
