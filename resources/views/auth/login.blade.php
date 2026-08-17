@extends('layouts.auth')

@section('title', 'Log in')
@section('heading', 'Welcome back')
@section('subheading', 'Sign in to your Lara Fire account.')

@section('form')
    @include('auth.partials.social')

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">Password</label>
                <a href="{{ route('password.request') }}" class="small link-fire">Forgot password?</a>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-fire w-100">Log in</button>
    </form>
@endsection

@section('below')
    New here? <a href="{{ route('register') }}" class="link-fire">Create an account</a>
@endsection
