<?php

namespace App\Services;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Contract\Firestore;

/**
 * Per-user notes stored in Cloud Firestore: notes/{id} { uid, title, body, created_at, updated_at }.
 */
class NoteRepository
{
    public function __construct(protected Container $container) {}

    public static function available(): bool
    {
        return class_exists(FirestoreClient::class) && extension_loaded('grpc');
    }

    /**
     * @return list<array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}>
     */
    public function forUser(string $uid): array
    {
        $notes = [];
        foreach ($this->collection()->where('uid', '=', $uid)->documents() as $doc) {
            if ($doc->exists()) {
                $notes[] = $this->map($doc->id(), $doc->data());
            }
        }

        usort($notes, fn ($a, $b) => strcmp((string) $b['updated_at'], (string) $a['updated_at']));

        return $notes;
    }

    /**
     * Returns null when missing or owned by someone else.
     *
     * @return array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}|null
     */
    public function find(string $uid, string $id): ?array
    {
        $doc = $this->collection()->document($id)->snapshot();

        if (! $doc->exists() || ($doc->data()['uid'] ?? null) !== $uid) {
            return null;
        }

        return $this->map($doc->id(), $doc->data());
    }

    /**
     * @param  array{title: string, body?: ?string}  $data
     * @return array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}
     */
    public function create(string $uid, array $data): array
    {
        $now = Carbon::now()->toIso8601String();
        $payload = [
            'uid' => $uid,
            'title' => $data['title'],
            'body' => (string) ($data['body'] ?? ''),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $ref = $this->collection()->add($payload);

        return $this->map($ref->id(), $payload);
    }

    /**
     * @param  array{title: string, body?: ?string}  $data
     */
    public function update(string $uid, string $id, array $data): ?array
    {
        $existing = $this->find($uid, $id);
        if (! $existing) {
            return null;
        }

        $changes = [
            'title' => $data['title'],
            'body' => (string) ($data['body'] ?? ''),
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        $this->collection()->document($id)->set($changes, ['merge' => true]);

        return array_merge($existing, $changes);
    }

    public function delete(string $uid, string $id): bool
    {
        if (! $this->find($uid, $id)) {
            return false;
        }

        $this->collection()->document($id)->delete();

        return true;
    }

    protected function collection(): CollectionReference
    {
        return $this->container->make(Firestore::class)
            ->database()
            ->collection((string) config('larafire.firestore.notes_collection', 'notes'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}
     */
    protected function map(string $id, array $data): array
    {
        return [
            'id' => $id,
            'title' => (string) ($data['title'] ?? ''),
            'body' => (string) ($data['body'] ?? ''),
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }
}
