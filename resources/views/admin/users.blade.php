@extends('layouts.app')

@section('title', 'Admin · Users')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0">Admin</h1>
        <button class="btn btn-fire" data-bs-toggle="modal" data-bs-target="#createUser">+ Add user</button>
    </div>

    @include('admin.partials.tabs')

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="row g-3 mb-4">
        @foreach ([
            ['Total users', $stats['total'], 'text-body'],
            ['Verified', $stats['verified'], 'text-success'],
            ['New (30 days)', $stats['new30'], 'text-fire'],
            ['Admins', $stats['admins'], 'text-danger'],
            ['Disabled', $stats['disabled'], 'text-body-secondary'],
        ] as [$label, $value, $class])
            <div class="col-6 col-md">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="small text-body-secondary">{{ $label }}</div>
                        <div class="fs-3 fw-bold {{ $class }}">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-transparent">
                    <form method="GET" class="d-flex gap-2">
                        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, email or UID">
                        <button class="btn btn-sm btn-outline-secondary">Search</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-body-secondary">
                            <tr>
                                <th>User</th>
                                <th>Providers</th>
                                <th>Last sign-in</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $u)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @include('partials.avatar', ['user' => $u, 'size' => 32])
                                            <div>
                                                <div class="fw-semibold">
                                                    {{ $u->displayName ?: '—' }}
                                                    @if ($u->isAdmin())<span class="badge text-bg-danger ms-1">admin</span>@endif
                                                    @if ($u->uid === auth()->id())<span class="badge text-bg-secondary ms-1">you</span>@endif
                                                </div>
                                                <div class="small text-body-secondary">
                                                    {{ $u->email }}
                                                    @if ($u->emailVerified)<span title="Verified">✔</span>@endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small">{{ implode(', ', $u->providers) ?: '—' }}</td>
                                    <td class="small text-body-secondary">
                                        {{ $u->lastLoginAt ? \Illuminate\Support\Carbon::parse($u->lastLoginAt)->diffForHumans() : 'never' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $u->disabled ? 'text-bg-secondary' : 'text-bg-success' }}">{{ $u->disabled ? 'Disabled' : 'Active' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>Manage</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-{{ $u->uid }}">Edit details</button></li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.users.password-reset', $u->uid) }}">
                                                        @csrf
                                                        <button class="dropdown-item" @disabled(! $u->email)>Send password reset</button>
                                                    </form>
                                                </li>
                                                @if ($u->uid !== auth()->id())
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.users.toggle-admin', $u->uid) }}">
                                                            @csrf
                                                            <button class="dropdown-item">{{ $u->isAdmin() ? 'Revoke admin' : 'Make admin' }}</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.users.toggle-disabled', $u->uid) }}">
                                                            @csrf
                                                            <button class="dropdown-item {{ $u->disabled ? 'text-success' : 'text-danger' }}">
                                                                {{ $u->disabled ? 'Enable account' : 'Disable account' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>

                                        <div class="modal fade text-start" id="edit-{{ $u->uid }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form method="POST" action="{{ route('admin.users.update', $u->uid) }}" class="modal-content">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header">
                                                        <h2 class="modal-title h5">Edit {{ $u->displayName ?: $u->email }}</h2>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Name</label>
                                                            <input name="displayName" value="{{ $u->displayName }}" class="form-control" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input name="email" type="email" value="{{ $u->email }}" class="form-control" required>
                                                        </div>
                                                        <div class="small text-body-secondary">UID: <code>{{ $u->uid }}</code></div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-fire">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-body-secondary py-4">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6">Sign-in providers</h2>
                    @foreach ($providers as $name => $count)
                        <div class="d-flex justify-content-between small py-1 border-bottom">
                            <span>{{ $name }}</span><span class="fw-semibold">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="card-body small">
                    <h2 class="h6">System</h2>
                    <div class="d-flex justify-content-between py-1"><span>Laravel</span><span>{{ app()->version() }}</span></div>
                    <div class="d-flex justify-content-between py-1"><span>PHP</span><span>{{ PHP_VERSION }}</span></div>
                    <div class="d-flex justify-content-between py-1"><span>Firestore</span><span>{{ \App\Services\NoteRepository::available() ? 'enabled' : 'off' }}</span></div>
                    <div class="d-flex justify-content-between py-1"><span>FCM (web)</span><span>{{ filled(config('larafire.vapid_key')) ? 'enabled' : 'off' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5">Add user</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="new-name">Name</label>
                    <input id="new-name" name="displayName" value="{{ old('displayName') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-email">Email</label>
                    <input id="new-email" name="email" type="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-password">Temporary password</label>
                    <input id="new-password" name="password" type="password" class="form-control" minlength="8" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-fire">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
