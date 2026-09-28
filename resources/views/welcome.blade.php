@extends('layouts.app')

@section('content')
<div class="container">
    <section class="hero text-center py-5 mb-5">
        <span class="badge rounded-pill text-bg-light border mb-3 px-3 py-2">
            v2 · Laravel {{ \Illuminate\Support\Str::before(app()->version(), '.') }} + Firebase
        </span>
        <h1 class="display-4 fw-bold mb-3">Ship Laravel apps on <span class="text-fire">Firebase</span></h1>
        <p class="lead text-body-secondary mx-auto mb-4" style="max-width: 640px">
            Auth, admin roles, Firestore, push notifications and a token-secured API — wired up and ready to fork.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            @auth
                <a href="{{ route('home') }}" class="btn btn-fire btn-lg px-4">Open dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-fire btn-lg px-4">Create an account</a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">Log in</a>
            @endauth
            <a href="{{ config('larafire.repository_url') }}" target="_blank" rel="noopener" class="btn btn-link btn-lg">GitHub ↗</a>
        </div>
    </section>

    <section class="row g-4 mb-5">
        @foreach ([
            ['🔐', 'Firebase Auth', 'Email + password, Google and GitHub sign-in, email verification and password reset.'],
            ['🛡️', 'Admin panel', 'Manage users, toggle admin custom claims, disable accounts and send reset links.'],
            ['🗂️', 'Cloud Firestore', 'A per-user Notes module showing real CRUD against Firestore.'],
            ['🔔', 'Push (FCM)', 'Opt in from the browser; admins broadcast or target a single user.'],
            ['🔌', 'REST API', '/api/v1 secured with Firebase ID tokens — ready for mobile apps.'],
            ['🌙', 'Modern UI', 'Bootstrap 5.3 on Vite 8 with dark mode and a clean admin layout.'],
        ] as [$icon, $title, $text])
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100 feature-card">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-3">{{ $icon }}</div>
                        <h2 class="h5">{{ $title }}</h2>
                        <p class="text-body-secondary mb-0">{{ $text }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</div>
@endsection
