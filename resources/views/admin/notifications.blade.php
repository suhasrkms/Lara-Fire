@extends('layouts.app')

@section('title', 'Admin · Push')

@section('content')
<div class="container">
    <h1 class="h3 fw-bold mb-3">Admin</h1>
    @include('admin.partials.tabs')

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Send a push notification</h2>
                    @unless ($configured)
                        <div class="alert alert-warning small">
                            <code>FIREBASE_WEB_VAPID_KEY</code> isn't set, so browsers can't subscribe yet. Messages will still be accepted by FCM.
                        </div>
                    @endunless
                    <form method="POST" action="{{ route('admin.notifications.store') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label d-block">Audience</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="audience" id="aud-all" value="all" @checked(old('audience', 'all') === 'all')>
                                <label class="btn btn-outline-secondary" for="aud-all">Everyone</label>
                                <input type="radio" class="btn-check" name="audience" id="aud-user" value="user" @checked(old('audience') === 'user')>
                                <label class="btn btn-outline-secondary" for="aud-user">One user</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="uid" class="form-label">User UID <span class="text-body-secondary small">(for “One user”)</span></label>
                            <input id="uid" name="uid" value="{{ old('uid') }}" class="form-control @error('uid') is-invalid @enderror" placeholder="Copy from the Users tab">
                            @error('uid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input id="title" name="title" value="{{ old('title') }}" maxlength="100" class="form-control @error('title') is-invalid @enderror" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Message</label>
                            <textarea id="body" name="body" rows="3" maxlength="500" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="link" class="form-label">Link <span class="text-body-secondary small">(optional)</span></label>
                            <input id="link" name="link" type="url" value="{{ old('link') }}" class="form-control @error('link') is-invalid @enderror" placeholder="https://example.com/page">
                            @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button class="btn btn-fire">Send</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-4 small">
                    <h2 class="h6">How it works</h2>
                    <p class="text-body-secondary">Browsers that click <em>Enable on this device</em> on the dashboard are subscribed to two FCM topics:</p>
                    <ul class="text-body-secondary">
                        <li><code>{{ config('larafire.fcm.broadcast_topic') }}</code> — everyone</li>
                        <li><code>{{ config('larafire.fcm.user_topic_prefix') }}&lt;uid&gt;</code> — that user</li>
                    </ul>
                    <p class="text-body-secondary mb-0">No tokens are stored in your database.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
