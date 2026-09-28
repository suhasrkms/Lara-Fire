<?php

namespace Tests\Feature;

use App\Services\NoteRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotesTest extends TestCase
{
    private const BASE = 'https://firestore.googleapis.com/v1/projects/demo/databases/(default)/documents';

    protected function setUp(): void
    {
        parent::setUp();

        config(['larafire.firestore.project_id' => 'demo']);
        Cache::put(NoteRepository::TOKEN_CACHE_KEY, 'test-token', 60);
    }

    private function doc(string $id, string $uid, string $title): array
    {
        return [
            'name' => "projects/demo/databases/(default)/documents/notes/{$id}",
            'fields' => [
                'uid' => ['stringValue' => $uid],
                'title' => ['stringValue' => $title],
                'body' => ['stringValue' => 'Body'],
                'created_at' => ['stringValue' => '2026-09-01T10:00:00+00:00'],
                'updated_at' => ['stringValue' => '2026-09-01T10:00:00+00:00'],
            ],
        ];
    }

    public function test_notes_index_lists_only_the_users_notes(): void
    {
        Http::fake([
            self::BASE.':runQuery' => Http::response([
                ['document' => $this->doc('n1', 'uid-123', 'My first note')],
                ['readTime' => '2026-09-01T10:00:00Z'],
            ]),
        ]);

        $this->actingAs($this->firebaseUser())->get('/notes')->assertOk()->assertSee('My first note');

        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer test-token')
            && $r['structuredQuery']['where']['fieldFilter']['value']['stringValue'] === 'uid-123');
    }

    public function test_cannot_edit_someone_elses_note(): void
    {
        Http::fake([self::BASE.'/notes/n2' => Http::response($this->doc('n2', 'someone-else', 'Secret'))]);

        $this->actingAs($this->firebaseUser())->get('/notes/n2/edit')->assertNotFound();
    }

    public function test_create_note(): void
    {
        Http::fake([self::BASE.'/notes' => Http::response($this->doc('new1', 'uid-123', 'Hello'))]);

        $this->actingAs($this->firebaseUser())
            ->post('/notes', ['title' => 'Hello', 'body' => 'World'])
            ->assertRedirect('/notes');

        Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['fields']['uid']['stringValue'] === 'uid-123');
    }

    public function test_missing_database_shows_setup_page_instead_of_crashing(): void
    {
        Http::fake([self::BASE.':runQuery' => Http::response(['error' => ['message' => 'The database (default) does not exist']], 404)]);

        $this->actingAs($this->firebaseUser())->get('/notes')->assertOk()->assertSee('Firestore database not found');
    }
}
