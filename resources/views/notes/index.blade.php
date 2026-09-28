@extends('layouts.app')

@section('title', 'Notes')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Notes</h1>
            <p class="text-body-secondary mb-0 small">Stored in Cloud Firestore → <code>{{ config('larafire.firestore.notes_collection') }}</code></p>
        </div>
        <a href="{{ route('notes.create') }}" class="btn btn-fire">+ New note</a>
    </div>

    <div class="row g-4">
        @forelse ($notes as $note)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold">{{ $note['title'] }}</h2>
                        <p class="small text-body-secondary mb-0 note-body">{{ \Illuminate\Support\Str::limit($note['body'], 220) }}</p>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center small">
                        <span class="text-body-secondary">
                            {{ $note['updated_at'] ? \Illuminate\Support\Carbon::parse($note['updated_at'])->diffForHumans() : '' }}
                        </span>
                        <span class="d-flex gap-2">
                            <a href="{{ route('notes.edit', $note['id']) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('notes.destroy', $note['id']) }}" onsubmit="return confirm('Delete this note?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-body text-center py-5 text-body-secondary">
                    No notes yet. <a href="{{ route('notes.create') }}" class="link-fire">Write your first one →</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
