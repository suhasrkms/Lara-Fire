@php($labels = ['google' => ['Google', 'G'], 'github' => ['GitHub', '']])
@if (count($socialProviders) && filled(config('larafire.web.apiKey')))
    <div class="d-grid gap-2 mb-3">
        @foreach ($socialProviders as $provider)
            @continue(! isset($labels[$provider]))
            <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2"
                    data-social-login="{{ $provider }}">
                <span class="social-icon social-{{ $provider }}" aria-hidden="true"></span>
                Continue with {{ $labels[$provider][0] }}
            </button>
        @endforeach
    </div>
    <form id="social-login-form" method="POST" action="{{ route('auth.firebase') }}" class="d-none">
        @csrf
        <input type="hidden" name="id_token">
    </form>
    <div class="text-danger small mb-2 d-none" data-social-error></div>
    <div class="divider text-body-secondary small my-3"><span>or with email</span></div>
@endif
