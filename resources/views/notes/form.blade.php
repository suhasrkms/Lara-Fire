@extends('layouts.app')

@section('title', $note ? 'Edit note' : 'New note')

@section('content')
<div class="container" style="max-width: 760px">
    <a href="{{ route('notes.index') }}" class="small link-secondary">← All notes</a>
    <h1 class="h3 fw-bold my-3">{{ $note ? 'Edit note' : 'New note' }}</h1>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ $note ? route('notes.update', $note['id']) : route('notes.store') }}" novalidate>
                @csrf
                @if ($note) @method('PUT') @endif
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $note['title'] ?? '') }}" required autofocus
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="body" class="form-label">Body</label>
                    <textarea id="body" name="body" rows="8" class="form-control @error('body') is-invalid @enderror">{{ old('body', $note['body'] ?? '') }}</textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-fire">{{ $note ? 'Save changes' : 'Create note' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
