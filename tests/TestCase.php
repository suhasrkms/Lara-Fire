<?php

namespace Tests;

use App\Auth\FirebaseUser;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;
use Mockery;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // No real network calls or built assets needed in tests.
        Http::preventStrayRequests();
        $this->withoutVite();
    }

    /** Bind a Mockery double for the Firebase Auth contract. */
    protected function mockFirebaseAuth(): MockInterface
    {
        $mock = Mockery::mock(FirebaseAuth::class);
        $this->app->instance(FirebaseAuth::class, $mock);

        return $mock;
    }

    /** @param array<string, mixed> $overrides */
    protected function userRecord(array $overrides = []): UserRecord
    {
        $claims = $overrides['claims'] ?? [];
        unset($overrides['claims']);

        return UserRecord::fromResponseData(array_merge([
            'localId' => 'uid-123',
            'email' => 'jane@example.com',
            'displayName' => 'Jane Doe',
            'emailVerified' => true,
            'disabled' => false,
            'createdAt' => (string) (now()->subDays(3)->getTimestamp() * 1000),
            'lastLoginAt' => (string) (now()->getTimestamp() * 1000),
            'providerUserInfo' => [['providerId' => 'password', 'rawId' => 'jane@example.com', 'email' => 'jane@example.com']],
            'customAttributes' => json_encode((object) $claims),
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    protected function firebaseUser(array $overrides = []): FirebaseUser
    {
        return FirebaseUser::fromRecord($this->userRecord($overrides));
    }

    /** @param array<string, mixed> $claims */
    protected function idToken(array $claims): UnencryptedToken
    {
        $token = Mockery::mock(UnencryptedToken::class);
        $token->allows('claims')->andReturn(new DataSet($claims, ''));

        return $token;
    }
}
