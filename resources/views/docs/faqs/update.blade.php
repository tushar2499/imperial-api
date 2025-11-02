@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">API: Update Faq</h1>

        <h3>Request</h3>
        <p>Update the details of a specific faq by ID:</p>
        <pre><code>PUT /faqs/{id}</code></pre>

        <h3>Request Body Parameters:</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Parameter</th>
                        <th width="50%">Validation Rule</th>
                        <th width="30%">Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>question</code></td>
                        <td>Required | String | Max: 255</td>
                        <td>faq question</td>
                    </tr>
                    <tr>
                        <td><code>answer</code></td>
                        <td>Nullable | String </td>
                        <td>faq question answer</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4>Sample Response:</h4>
        <div class="card">
            <div class="card-body">
                <pre>
                    <code>
    {
        "status": "success",
        "message": "Faq updated successfully",
        "data": {
            "id": 1,
            "question": "Faq question",
            "answer": "Faq answer",
            "status": 1,
            "created_by": 1,
            "updated_by": null,
            "created_at": "2025-11-02T05:29:58.000000Z",
            "updated_at": "2025-11-02T05:29:58.000000Z",
            "deleted_at": null
        }
    }
                    </code>
                </pre>
            </div>
        </div>
    </div>
@endsection
