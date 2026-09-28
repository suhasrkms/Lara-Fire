<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link @if (request()->routeIs('admin.users.*')) active @endif" href="{{ route('admin.users.index') }}">Users</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if (request()->routeIs('admin.notifications.*')) active @endif" href="{{ route('admin.notifications.create') }}">Push notifications</a>
    </li>
</ul>
