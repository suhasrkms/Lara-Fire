<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Per-user notes in Cloud Firestore via the REST API (no gRPC extension needed).
 *
 * notes/{id} { uid, title, body, created_at, updated_at }
 */
class NoteRepository
{
    public const TOKEN_CACHE_KEY = 'larafire.firestore.token';

    /**
     * True when a service account with a project ID is configured.
     */
    public static function available(): bool
    {
        return filled(self::projectId());
    }

    /**
     * @return list<array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}>
     */
    public function forUser(string $uid): array
    {
        $response = $this->request()->post($this->documentsUrl().':runQuery', [
            'structuredQuery' => [
                'from' => [['collectionId' => $this->collection()]],
                'where' => ['fieldFilter' => [
                    'field' => ['fieldPath' => 'uid'],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $uid],
                ]],
            ],
        ]);
        $this->ensureOk($response);

        $notes = [];
        foreach ((array) $response->json() as $row) {
            if (isset($row['document'])) {
                $notes[] = $this->map($row['document']);
            }
        }

        usort($notes, fn ($a, $b) => strcmp((string) $b['updated_at'], (string) $a['updated_at']));

        return $notes;
    }

    /**
     * Null when missing or owned by someone else.
     *
     * @return array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}|null
     */
    public function find(string $uid, string $id): ?array
    {
        if (! $this->validId($id)) {
            return null;
        }

        $response = $this->request()->get($this->docUrl($id));

        if ($response->status() === 404) {
            return null;
        }
        $this->ensureOk($response);

        $doc = $response->json();

        return ($this->decode($doc['fields'] ?? [])['uid'] ?? null) === $uid ? $this->map($doc) : null;
    }

    /**
     * @param  array{title: string, body?: ?string}  $data
     * @return array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}
     */
    public function create(string $uid, array $data): array
    {
        $now = Carbon::now()->toIso8601String();

        $response = $this->request()->post($this->documentsUrl().'/'.$this->collection(), [
            'fields' => $this->encode([
                'uid' => $uid,
                'title' => $data['title'],
                'body' => (string) ($data['body'] ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ]);
        $this->ensureOk($response);

        return $this->map($response->json());
    }

    /**
     * @param  array{title: string, body?: ?string}  $data
     */
    public function update(string $uid, string $id, array $data): ?array
    {
        if (! $this->find($uid, $id)) {
            return null;
        }

        $changes = [
            'title' => $data['title'],
            'body' => (string) ($data['body'] ?? ''),
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        $mask = implode('&', array_map(fn ($f) => 'updateMask.fieldPaths='.$f, array_keys($changes)));

        $response = $this->request()->patch($this->docUrl($id).'?'.$mask, ['fields' => $this->encode($changes)]);
        $this->ensureOk($response);

        return $this->map($response->json());
    }

    public function delete(string $uid, string $id): bool
    {
        if (! $this->find($uid, $id)) {
            return false;
        }

        $this->ensureOk($this->request()->delete($this->docUrl($id)));

        return true;
    }

    // ---------------------------------------------------------------------

    protected static function projectId(): ?string
    {
        if ($id = config('larafire.firestore.project_id')) {
            return (string) $id;
        }

        $key = self::serviceAccount();

        return isset($key['project_id']) ? (string) $key['project_id'] : null;
    }

    /** @return array<string, mixed>|null */
    protected static function serviceAccount(): ?array
    {
        $credentials = (string) config('firebase.projects.app.credentials');

        if ($credentials === '') {
            return null;
        }

        if (str_starts_with(ltrim($credentials), '{')) {
            return json_decode($credentials, true) ?: null;
        }

        $path = str_starts_with($credentials, '/') || str_contains($credentials, ':\\') || str_contains($credentials, ':/')
            ? $credentials
            : base_path($credentials);

        return is_file($path) ? (json_decode((string) file_get_contents($path), true) ?: null) : null;
    }

    protected function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function () {
            $key = self::serviceAccount() ?? throw new RuntimeException('Firebase service account not found.');

            $token = (new ServiceAccountCredentials('https://www.googleapis.com/auth/datastore', $key))->fetchAuthToken();

            return $token['access_token'] ?? throw new RuntimeException('Could not get a Google access token.');
        });
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->accessToken())->acceptJson()->timeout(10);
    }

    protected function documentsUrl(): string
    {
        return 'https://firestore.googleapis.com/v1/projects/'.rawurlencode((string) self::projectId())
            .'/databases/'.$this->databaseId().'/documents';
    }

    /** "(default)" is sent literally, as in Google's own URLs. */
    protected function databaseId(): string
    {
        $id = (string) config('larafire.firestore.database', '(default)');

        return preg_match('/^(\(default\)|[a-z][a-z0-9-]{3,62})$/', $id) ? $id : '(default)';
    }

    protected function docUrl(string $id): string
    {
        return $this->documentsUrl().'/'.$this->collection().'/'.rawurlencode($id);
    }

    protected function collection(): string
    {
        return rawurlencode((string) config('larafire.firestore.notes_collection', 'notes'));
    }

    protected function validId(string $id): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_-]{1,128}$/', $id);
    }

    protected function ensureOk(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $message = $response->json('error.message') ?? $response->body();

        if ($response->status() === 404 || str_contains((string) $message, 'does not exist')) {
            $message = 'Firestore database not found. Create it in Firebase console → Firestore Database. ('.$message.')';
        }

        throw new RuntimeException('Firestore: '.$message, $response->status());
    }

    /**
     * @param  array<string, string>  $values
     * @return array<string, array{stringValue: string}>
     */
    protected function encode(array $values): array
    {
        return array_map(fn ($v) => ['stringValue' => (string) $v], $values);
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    protected function decode(array $fields): array
    {
        return array_map(fn ($f) => $f['stringValue'] ?? $f['timestampValue'] ?? $f['integerValue'] ?? null, $fields);
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return array{id: string, title: string, body: string, created_at: ?string, updated_at: ?string}
     */
    protected function map(array $doc): array
    {
        $data = $this->decode($doc['fields'] ?? []);

        return [
            'id' => basename((string) ($doc['name'] ?? '')),
            'title' => (string) ($data['title'] ?? ''),
            'body' => (string) ($data['body'] ?? ''),
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }
}
