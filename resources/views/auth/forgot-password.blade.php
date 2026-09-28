@extends('layouts.auth')

@section('title', 'Reset password')
@section('heading', 'Reset your password')
@section('subheading', "Enter your email and Firebase will send you a reset link.")

@section('form')
    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-fire w-100">Send reset link</button>
    </form>
@endsection

@section('below')
    <a href="{{ route('login') }}" class="link-fire">← Back to log in</a>
@endsection
