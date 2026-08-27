@extends('layouts.app')

@section('title', 'Notes')

@section('content')
<div class="container" style="max-width: 760px">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 fw-bold">Enable Cloud Firestore</h1>
            <p class="text-body-secondary">The Notes module needs the Firestore client, which depends on the <code>grpc</code> PHP extension.</p>
            <ol class="mb-0">
                <li>Install ext-grpc (<code>pecl install grpc</code>, or the DLL on Windows) and enable it in <code>php.ini</code>.</li>
                <li>Run <code>composer require google/cloud-firestore</code>.</li>
                <li>Create a Firestore database in the Firebase console.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
