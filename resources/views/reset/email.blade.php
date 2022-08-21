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
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Verify Your Email Address</div>

          <div class="card-body">
            @if(Session::has('error'))
              <p class=" pb-3 alert {{ Session::get('alert-class', 'alert-danger') }} " style="text-transform: capitalize;">{{ Session::get('error') }}</p>
            @else
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ __('A fresh verification link has been sent to your email address.') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            Before proceeding, please check your email for a verification link.
            If you did not receive the email

            <a href="/home" style="text-decoration:none;">{{ __('click here to request another') }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
