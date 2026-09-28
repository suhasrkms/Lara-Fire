<nav class="navbar navbar-expand-md bg-body border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
            <span class="brand-mark">🔥</span> {{ config('app.name', 'Lara Fire') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('home')) active @endif" href="{{ route('home') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('notes.*')) active @endif" href="{{ route('notes.index') }}">Notes</a></li>
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link @if (request()->routeIs('admin.*')) active @endif" href="{{ route('admin.users.index') }}">Admin</a></li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav align-items-md-center gap-md-2">
                <li class="nav-item">
                    <button type="button" class="btn btn-link nav-link px-2" data-theme-toggle aria-label="Toggle dark mode">
                        <span class="theme-icon-light">🌙</span><span class="theme-icon-dark">☀️</span>
                    </button>
                </li>
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log in</a></li>
                    <li class="nav-item"><a class="btn btn-fire btn-sm px-3" href="{{ route('register') }}">Get started</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @include('partials.avatar', ['user' => auth()->user(), 'size' => 28])
                            <span>{{ auth()->user()->name() }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Log out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
