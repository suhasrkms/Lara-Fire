<?php

namespace Tests\Feature;

use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_me_requires_a_bearer_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->mockFirebaseAuth()->allows('verifyIdToken')->andThrow(new FailedToVerifyToken('bad'));

        $this->withToken('bad')->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_me_returns_the_token_owner(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->allows('verifyIdToken')->with('good', true)->andReturn($this->idToken(['sub' => 'uid-123']));
        $auth->allows('getUser')->with('uid-123')->andReturn($this->userRecord());

        $this->withToken('good')->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.uid', 'uid-123')
            ->assertJsonPath('data.isAdmin', false);
    }
}
