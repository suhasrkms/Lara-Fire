@extends('layouts.app')
@section('navbar_home')
  @if (Route::has('login'))
          @auth
            <li class="nav-item">
              <a class="nav-link text-dark" href="{{ url('/home') }}">Home</a>
            </li>
          @else
              <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('login') }}">Login</a>
              </li>

              @if (Route::has('register'))
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('register') }}">Register</a>
                  </li>
              @endif
          @endauth
    @else
  @endif
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    {{ __('You are in home !') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
