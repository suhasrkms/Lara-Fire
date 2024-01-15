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
    /* hide scrollbar but allow scrolling */
    body {
    -ms-overflow-style: none; /* for Internet Explorer, Edge */
    scrollbar-width: none; /* for Firefox */
    overflow-y: scroll;
    }

    body::-webkit-scrollbar {
    display: none; /* for Chrome, Safari, and Opera */
    }

    .list-group-item.active {
        background-color: rgba(232,236,238,255) !important;
        color: black;
    }
</style>
<!-- DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
<script>
    $(document).ready(function () {
        $('#usersTable').DataTable({
            "order": [[1, "desc"]], // Sort by the second column (index 1) in descending order
            "searching": false, // Disable search
            "lengthChange": false, // Disable the ability to change number of entries per page
            "pageLength": 10, // Set the default number of entries to display
            "paging": false, // Hide pagination control
            "info" : false,
        });
    });
</script>


<div class="container">
    <!-- <h5 class="font-weight-bold">Dashboard</h5> -->
    <div class="row">
      <!-- Sidebar -->
        <div class="col-md-3 col-sm-12 p-3">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action mb-3 border active" id="dashboardLink" onclick="showDashboard()">Dashboard</a>
                <div class="dropdown">
                    <a href="#" class="list-group-item list-group-item-action mb-3 border dropdown-toggle d-flex justify-content-between align-items-center" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Users
                        {{-- <span class="dropdown-arrow ml-auto">&#9662;</span> --}}
                    </a>
                    <div class="dropdown-menu w-100 p-2" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="#" onclick="showUser()">View Users</a>
                        <a class="dropdown-item" href="#" onclick="addUser()">Add User</a>
                        <!-- Add other dropdown items as needed -->
                    </div>
                </div>
                {{-- <a href="#" class="list-group-item list-group-item-action mb-3 border" id="dummyLink" onclick="showDummy()">Dummy</a> --}}
            </div>
            <!-- Add more dashboard elements here -->
        </div>


      <!-- Dashboard Content -->

      <div class="col-md-9 col-sm-12 p-3">
        <div class="card rounded" id="dashboardContent">
          <h5 class="card-header">Dashboard Content</h5>
          <div class="card-body row">

            <!-- Total Users Count -->
            <div class="col-3">
                <div class="card rounded text-center h-100">
                    <div class="card-header bg-warning">Total Users</div>
                    <div class="card-body display-4">08</div>
                </div>
            </div>

            <!-- Total Users Count -->
            <div class="col-9">
                <div class="card rounded h-100">
                    <div class="card-header bg-success text-center">System Information</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-left"><strong>Laravel Version</strong></div>
                            <div class="col-8">: &ThinSpace; {{ Illuminate\Foundation\Application::VERSION }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 text-left"><strong>PHP Version</strong></div>
                            <div class="col-8">: &ThinSpace; {{ PHP_VERSION }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 text-left"><strong>Database</strong></div>
                            <div class="col-8">: &ThinSpace; Firebase</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users Count -->
            <div class="col-12 pt-3">
                <div class="card rounded ">
                    <div class="card-header bg-info text-white">Recent Active Users</div>
                    <table id="usersTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Logged-In</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->displayName }}</td>
                                    <td>{{ Carbon\Carbon::parse($user->metadata->lastLoginAt)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!--  -->
          </div>
        </div>

        {{-- User Content --}}
        <div class="card rounded" id="userContent">
          <h5 class="card-header">User Content</h5>
          <!-- Total Users Count -->
            <div class="col-12 pt-3">
                <div class="card rounded ">
                <div class="card-header bg-info text-white">Recent Active Users</div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">First Name</th>
                            <th scope="col">Last Logged-In</th>
                            <th scope="col">Email</th>
                            <th scope="col">Email Verified</th>
                            <th scope="col">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->displayName }}</td>
                                <td>{{ Carbon\Carbon::parse($user->metadata->lastLoginAt)->diffForHumans() }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->emailVerified ? 'Yes' : 'No' }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#{{$user->uid}}">
                                      Edit
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="{{$user->uid}}" tabindex="-1" role="dialog" aria-labelledby="{{$user->uid}}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                {{ $user->displayName }}
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary">Save changes</button>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- <div class="card rounded" id="dummyContent">
          <h5 class="card-header">Dummy Content</h5>
          <p class="card-body">This is dummy content for demonstration purposes.</p>
        </div> --}}
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
    // window.onload = showDashboard;
  </script>


@endsection
