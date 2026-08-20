@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="d-flex align-items-center gap-3 mb-4">
        @include('partials.avatar', ['user' => $user, 'size' => 56])
        <div>
            <h1 class="h3 fw-bold mb-0">Hi, {{ $user->name() }} 👋</h1>
            <p class="text-body-secondary mb-0">
                {{ $user->email }}
                @if ($user->isAdmin()) · <span class="badge text-bg-danger">admin</span>@endif
            </p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="{{ route('notes.index') }}" class="card h-100 quick-card text-decoration-none">
                <div class="card-body p-4">
                    <div class="feature-icon mb-2">🗂️</div>
                    <h2 class="h6 text-body">Firestore notes</h2>
                    <p class="small text-body-secondary mb-0">Create, edit and delete notes stored in Cloud Firestore.</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('profile.edit') }}" class="card h-100 quick-card text-decoration-none">
                <div class="card-body p-4">
                    <div class="feature-icon mb-2">👤</div>
                    <h2 class="h6 text-body">Profile</h2>
                    <p class="small text-body-secondary mb-0">Update name, email or password.</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            @if ($user->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="card h-100 quick-card text-decoration-none">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-2">🛡️</div>
                        <h2 class="h6 text-body">Admin panel</h2>
                        <p class="small text-body-secondary mb-0">Users, roles and push notifications.</p>
                    </div>
                </a>
            @else
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-2">🛡️</div>
                        <h2 class="h6">Want admin access?</h2>
                        <p class="small text-body-secondary mb-0">Run <code>php artisan larafire:make-admin {{ $user->email }}</code> on the server.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">🔔 Push notifications</h2>
                    @if ($fcmEnabled)
                        <p class="text-body-secondary small">Get messages sent from the admin panel on this browser.</p>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <button type="button" class="btn btn-fire btn-sm" data-push-enable>Enable on this device</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm d-none" data-push-disable>Disable</button>
                            <span class="small text-body-secondary" data-push-status></span>
                        </div>
                    @else
                        <p class="text-body-secondary small mb-0">
                            Set <code>FIREBASE_WEB_*</code> and <code>FIREBASE_WEB_VAPID_KEY</code> in <code>.env</code> to enable FCM.
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">🔌 Try the API</h2>
                    <p class="text-body-secondary small">Call <code>/api/v1/me</code> with your Firebase ID token.</p>
<pre class="small bg-body-tertiary rounded p-3 mb-0"><code>curl {{ url('/api/v1/me') }} \
  -H "Authorization: Bearer &lt;ID_TOKEN&gt;"</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
