<?php

namespace App\Http\Controllers;

use App\Services\NoteRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(protected NoteRepository $notes) {}

    public function index(Request $request): View
    {
        if (! NoteRepository::available()) {
            return view('notes.unavailable');
        }

        try {
            $notes = $this->notes->forUser($request->user()->uid);
        } catch (\RuntimeException $e) {
            report($e);

            return view('notes.unavailable', ['error' => $e->getMessage()]);
        }

        return view('notes.index', ['notes' => $notes]);
    }

    public function create(): View
    {
        $this->ensureAvailable();

        return view('notes.form', ['note' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAvailable();

        $this->notes->create($request->user()->uid, $this->validated($request));

        return redirect()->route('notes.index')->with('status', 'Note saved to Firestore.');
    }

    public function edit(Request $request, string $note): View
    {
        $this->ensureAvailable();

        $found = $this->notes->find($request->user()->uid, $note);
        abort_unless($found, 404);

        return view('notes.form', ['note' => $found]);
    }

    public function update(Request $request, string $note): RedirectResponse
    {
        $this->ensureAvailable();

        abort_unless($this->notes->update($request->user()->uid, $note, $this->validated($request)), 404);

        return redirect()->route('notes.index')->with('status', 'Note updated.');
    }

    public function destroy(Request $request, string $note): RedirectResponse
    {
        $this->ensureAvailable();

        abort_unless($this->notes->delete($request->user()->uid, $note), 404);

        return redirect()->route('notes.index')->with('status', 'Note deleted.');
    }

    protected function ensureAvailable(): void
    {
        abort_unless(NoteRepository::available(), 503, 'Firestore is not configured. See README → Firestore.');
    }

    /** @return array{title: string, body: ?string} */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}
