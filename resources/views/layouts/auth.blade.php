@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 fw-bold mb-1">@yield('heading')</h1>
                    <p class="text-body-secondary mb-4">@yield('subheading')</p>
                    @yield('form')
                </div>
            </div>
            <div class="text-center mt-3 small text-body-secondary">@yield('below')</div>
        </div>
    </div>
</div>
@endsection
