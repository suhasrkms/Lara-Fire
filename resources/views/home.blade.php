@extends('layouts.app')
@section('navbar_home')
  @guest
    @if (Route::has('login'))
      <li class="nav-item">
        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
      </li>
    @endif

    @if (Route::has('register'))
      <li class="nav-item">
        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
      </li>
    @endif
  @else

    @if($user->customClaims['admin'])
        <li class="nav-item">
        <a class="nav-link text-dark" href="/home/admin">{{ __('Admin') }}</a>
        </li>
    @endif

    <li class="nav-item">
      <a class="nav-link text-dark" href="/home/profile">{{ __('Profile') }}</a>
    </li>

    <li class="nav-item">
      <a class="nav-link text-dark" href="{{ route('logout') }}"
      onclick="event.preventDefault();
      document.getElementById('logout-form').submit();">
      {{ __('Logout') }}
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
      @csrf
    </form>
  </li>
</div>
</li>
@endguest
@endsection
@section('content')
<div class="container">

    @if(Session::has('message'))
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ Session::get('message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    @if(Session::has('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ Session::get('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($totalUsers == 1)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Admin Panel is now available! Become an admin by visiting  <mark class="bg-ligth">/home/iamadmin</mark> or simply <a href="/home/iamadmin">click here</a>.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                    <h1>{{ $user->customClaims["admin"] }}</h1>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
