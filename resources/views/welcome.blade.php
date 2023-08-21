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
        <div class="card-header">{{ __('Welcome') }}</div>

        <div class="card-body">
          <div class="container text-center mt-5">
            <h1 class="display-5">Welcome to our <a href="https://github.com/suhasrkms/Lara-Fire">Lara-Fire</a> <span>&#127881;</span></h1>
            <p class="lead">We've seamlessly integrated Firebase into our platform, enabling powerful features like <span class="text-danger">Firestore</span>, <span class="text-danger">Authentication</span>, <span class="text-danger">Email Verification</span>, and an <span class="text-danger">Admin Panel</span>. With Firebase, we ensure real-time data updates, secure user authentication, and efficient administration. Join us as we leverage Firebase to deliver an exceptional user experience.</p>
            <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
          </div>                        
             </div>
      </div>
    </div>
  </div>
</div>
@endsection