<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel + Firebase starter') · {{ config('app.name', 'Lara Fire') }}</title>
    @php
        $larafireClient = [
            'firebase' => $firebaseWebConfig ?? [],
            'vapidKey' => config('larafire.vapid_key'),
            'routes' => [
                'firebaseLogin' => route('auth.firebase'),
                'pushSubscribe' => route('push.subscribe'),
                'pushUnsubscribe' => route('push.unsubscribe'),
                'serviceWorker' => route('fcm.sw'),
            ],
        ];
    @endphp
    <script>
        (() => {
            try {
                const saved = localStorage.getItem('larafire-theme');
                const dark = saved ? saved === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.dataset.bsTheme = dark ? 'dark' : 'light';
            } catch (e) {}
        })();
        window.LaraFire = {{ \Illuminate\Support\Js::from($larafireClient) }};
    </script>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    @include('partials.nav')

    <main class="flex-grow-1 py-4 py-lg-5">
        <div class="container">
            @include('partials.flash')
        </div>
        @yield('content')
    </main>

    <footer class="border-top py-4 mt-auto small text-body-secondary">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
            <span>
                <span class="text-fire fw-semibold">Lara Fire</span> · Laravel {{ app()->version() }} + Firebase
            </span>
            <span>
                Made with ❤️ by
                <a href="{{ config('larafire.youtube_url') }}" target="_blank" rel="noopener" class="link-secondary">Seven Stac</a>
                · <a href="{{ config('larafire.repository_url') }}" target="_blank" rel="noopener" class="link-secondary">GitHub</a>
            </span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
