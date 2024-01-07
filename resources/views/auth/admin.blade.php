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
      <a class="nav-link text-dark" href="{{ url('/home') }}">Home</a>
    </li>

    <li class="nav-item">
      <a class="nav-link text-dark active" href="{{ route('logout') }}"
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
<style>
  .list-group-item.active {
    background-color: rgba(232,236,238,255) !important;
    color: black;
  }
</style>

<div class="container">
    <!-- <h5 class="font-weight-bold">Dashboard</h5> -->
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-sm-12 p-3">
        <div class="list-group">
          <a href="#" class="list-group-item list-group-item-action mb-3 border active" id="dashboardLink" onclick="showDashboard()">Dashboard</a>
          <a href="#" class="list-group-item list-group-item-action mb-3 border" id="userLink" onclick="showUser()">User</a>
          <a href="#" class="list-group-item list-group-item-action mb-3 border" id="dummyLink" onclick="showDummy()">Dummy</a>
        </div>
        <!-- Add more dashboard elements here -->
      </div>

      <!-- Content -->
      <div class="col-md-9 col-sm-12 p-3">
        <div class="card rounded" id="dashboardContent">
          <h5 class="card-header">Dashboard Content</h5>
          <p class="card-body">Welcome to the Dashboard section. Here you can add your content.</p>
        </div>

        <div class="card rounded" id="userContent">
          <h5 class="card-header">User Content</h5>
          <p class="card-body">Welcome to the User section. Add user-related content here.</p>
        </div>

        <div class="card rounded" id="dummyContent">
          <h5 class="card-header">Dummy Content</h5>
          <p class="card-body">This is dummy content for demonstration purposes.</p>
        </div>
      </div>

    </div>
  </div>

  <script>
    function showDashboard() {
      document.getElementById('dashboardContent').style.display = 'block';
      document.getElementById('userContent').style.display = 'none';
      document.getElementById('dummyContent').style.display = 'none';

      document.getElementById('dashboardLink').classList.add('active');
      document.getElementById('userLink').classList.remove('active');
      document.getElementById('dummyLink').classList.remove('active');
    }

    function showUser() {
      document.getElementById('dashboardContent').style.display = 'none';
      document.getElementById('userContent').style.display = 'block';
      document.getElementById('dummyContent').style.display = 'none';

      document.getElementById('dashboardLink').classList.remove('active');
      document.getElementById('userLink').classList.add('active');
      document.getElementById('dummyLink').classList.remove('active');
    }

    function showDummy() {
      document.getElementById('dashboardContent').style.display = 'none';
      document.getElementById('userContent').style.display = 'none';
      document.getElementById('dummyContent').style.display = 'block';

      document.getElementById('dashboardLink').classList.remove('active');
      document.getElementById('userLink').classList.remove('active');
      document.getElementById('dummyLink').classList.add('active');
    }

    // Trigger the showDashboard function onload to display the Dashboard content by default
    window.onload = showDashboard;
  </script>


@endsection
