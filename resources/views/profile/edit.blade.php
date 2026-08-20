@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container" style="max-width: 760px">
    <h1 class="h3 fw-bold mb-4">Profile</h1>

    <div class="card mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Account details</h2>
            <form method="POST" action="{{ route('profile.update') }}" novalidate>
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label for="displayName" class="form-label">Name</label>
                    <input id="displayName" name="displayName" type="text" value="{{ old('displayName', $user->displayName) }}"
                           class="form-control @error('displayName') is-invalid @enderror" required>
                    @error('displayName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Changing your email requires verifying it again.</div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-body-secondary">
                        Sign-in methods: {{ implode(', ', $user->providers) ?: 'n/a' }}
                    </span>
                    <button type="submit" class="btn btn-fire">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">{{ $user->hasPasswordProvider() ? 'Change password' : 'Set a password' }}</h2>
            <form method="POST" action="{{ route('profile.password') }}" novalidate>
                @csrf
                @method('PUT')
                @if ($user->hasPasswordProvider())
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                               class="form-control @error('current_password', 'password') is-invalid @enderror">
                        @error('current_password', 'password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="password" class="form-label">New password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="form-control @error('password', 'password') is-invalid @enderror">
                        @error('password', 'password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label for="password_confirmation" class="form-label">Confirm</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control">
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-outline-secondary">Update password</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-danger-subtle">
        <div class="card-body p-4">
            <h2 class="h5 text-danger mb-2">Disable account</h2>
            <p class="small text-body-secondary">Your Firebase account will be disabled and you'll be signed out. An admin can re-enable it.</p>
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Disable your account?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Disable my account</button>
            </form>
        </div>
    </div>
</div>
@endsection
