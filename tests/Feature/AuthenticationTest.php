<?php

namespace Tests\Feature;

use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInResult;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_user_can_log_in_with_email_and_password(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->expects('signInWithEmailAndPassword')->with('jane@example.com', 'secret123')
            ->andReturn(SignInResult::fromData(['localId' => 'uid-123']));
        $auth->allows('getUser')->with('uid-123')->andReturn($this->userRecord());
        // Regression: login must not touch custom claims (v1 reset admin=false on every login).
        $auth->shouldNotReceive('setCustomUserClaims');

        $this->post('/login', ['email' => 'jane@example.com', 'password' => 'secret123'])
            ->assertRedirect('/home');

        $this->assertAuthenticated();
        $this->assertSame('uid-123', auth()->id());
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->mockFirebaseAuth()->allows('signInWithEmailAndPassword')
            ->andThrow(new FailedToSignIn('INVALID_LOGIN_CREDENTIALS'));

        $this->from('/login')
            ->post('/login', ['email' => 'jane@example.com', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_social_login_with_fresh_id_token(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->expects('verifyIdToken')->andReturn($this->idToken(['sub' => 'uid-123', 'auth_time' => time()]));
        $auth->allows('getUser')->andReturn($this->userRecord(['providerUserInfo' => [['providerId' => 'google.com', 'rawId' => 'g1']]]));

        $this->post('/auth/firebase', ['id_token' => 'token'])->assertRedirect('/home');
        $this->assertAuthenticated();
    }

    public function test_social_login_rejects_stale_token(): void
    {
        $this->mockFirebaseAuth()->expects('verifyIdToken')
            ->andReturn($this->idToken(['sub' => 'uid-123', 'auth_time' => time() - 3600]));

        $this->post('/auth/firebase', ['id_token' => 'token'])->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_unverified_password_user_is_sent_to_verification(): void
    {
        $this->actingAs($this->firebaseUser(['emailVerified' => false]))
            ->get('/home')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_social_user_without_verified_flag_can_use_app(): void
    {
        $user = $this->firebaseUser([
            'emailVerified' => false,
            'providerUserInfo' => [['providerId' => 'github.com', 'rawId' => 'gh1']],
        ]);

        $this->actingAs($user)->get('/home')->assertOk();
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs($this->firebaseUser())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
