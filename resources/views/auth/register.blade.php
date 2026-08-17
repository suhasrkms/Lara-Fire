@extends('layouts.auth')

@section('title', 'Register')
@section('heading', 'Create your account')
@section('subheading', 'Free, fast and backed by Firebase Auth.')

@section('form')
    @include('auth.partials.social')

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="password_confirmation" class="form-label">Confirm</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-fire w-100">Create account</button>
    </form>
@endsection

@section('below')
    Already registered? <a href="{{ route('login') }}" class="link-fire">Log in</a>
@endsection
