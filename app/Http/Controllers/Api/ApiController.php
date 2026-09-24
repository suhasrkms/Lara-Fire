<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function notes(Request $request, NoteRepository $notes): JsonResponse
    {
        $this->requireFirestore();

        return response()->json(['data' => $notes->forUser($request->user()->uid)]);
    }

    public function showNote(Request $request, NoteRepository $notes, string $id): JsonResponse
    {
        $this->requireFirestore();
        $note = $notes->find($request->user()->uid, $id);

        return $note
            ? response()->json(['data' => $note])
            : response()->json(['message' => 'Not found.'], 404);
    }

    public function storeNote(Request $request, NoteRepository $notes): JsonResponse
    {
        $this->requireFirestore();

        return response()->json(['data' => $notes->create($request->user()->uid, $this->validated($request))], 201);
    }

    public function updateNote(Request $request, NoteRepository $notes, string $id): JsonResponse
    {
        $this->requireFirestore();
        $note = $notes->update($request->user()->uid, $id, $this->validated($request));

        return $note
            ? response()->json(['data' => $note])
            : response()->json(['message' => 'Not found.'], 404);
    }

    public function destroyNote(Request $request, NoteRepository $notes, string $id): JsonResponse
    {
        $this->requireFirestore();

        return $notes->delete($request->user()->uid, $id)
            ? response()->json(null, 204)
            : response()->json(['message' => 'Not found.'], 404);
    }

    protected function requireFirestore(): void
    {
        abort_unless(NoteRepository::available(), 503, 'Firestore is not configured on this server.');
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
