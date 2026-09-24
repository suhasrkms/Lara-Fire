@extends('layouts.app')

@section('title', 'Notes')

@section('content')
<div class="container" style="max-width: 760px">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 fw-bold">Set up Cloud Firestore</h1>
            @isset($error)
                <div class="alert alert-danger small">{{ $error }}</div>
            @endisset
            <p class="text-body-secondary">Notes are stored in Cloud Firestore through its REST API. No PHP extensions needed.</p>
            <ol class="mb-0">
                <li>Put your service account JSON at <code>storage/app/firebase/service-account.json</code>.</li>
                <li>In the Firebase console open <strong>Firestore Database</strong> → <strong>Create database</strong>.</li>
                <li>Reload this page.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
