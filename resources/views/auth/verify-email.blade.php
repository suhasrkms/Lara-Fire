@extends('layouts.auth')

@section('title', 'Verify email')
@section('heading', 'Check your inbox')
@section('subheading')
    We sent a verification link to <strong>{{ $user->email }}</strong>. Click it, then come back here.
@endsection

@section('form')
    <div class="d-grid gap-2">
        <a href="{{ route('verification.notice') }}" class="btn btn-fire">I've verified — continue</a>
        <form method="POST" action="{{ route('verification.send') }}" class="d-grid">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Resend link</button>
        </form>
    </div>
@endsection

@section('below')
    <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-link btn-sm link-secondary p-0">Log out</button>
    </form>
@endsection
