<?php

namespace Tests\Unit;

use App\Auth\FirebaseUser;
use PHPUnit\Framework\TestCase;

class FirebaseUserTest extends TestCase
{
    public function test_admin_claim_must_be_strictly_true(): void
    {
        $this->assertTrue((new FirebaseUser('u1', customClaims: ['admin' => true]))->isAdmin());
        $this->assertFalse((new FirebaseUser('u1', customClaims: ['admin' => 'true']))->isAdmin());
        $this->assertFalse((new FirebaseUser('u1', customClaims: ['admin' => 1]))->isAdmin());
        $this->assertFalse((new FirebaseUser('u1'))->isAdmin());
    }

    public function test_password_provider_detection(): void
    {
        $this->assertTrue((new FirebaseUser('u1', providers: ['password']))->hasPasswordProvider());
        $this->assertFalse((new FirebaseUser('u1', providers: ['google.com']))->hasPasswordProvider());
        // No providers (e.g. anonymous) must still go through email verification.
        $this->assertTrue((new FirebaseUser('u1'))->hasPasswordProvider());
    }

    public function test_name_and_initials_fallbacks(): void
    {
        $this->assertSame('Jane Doe', (new FirebaseUser('u1', displayName: 'Jane Doe'))->name());
        $this->assertSame('JD', (new FirebaseUser('u1', displayName: 'Jane Doe'))->initials());
        $this->assertSame('jane', (new FirebaseUser('u1', email: 'jane@example.com'))->name());
        $this->assertSame('User', (new FirebaseUser('u1'))->name());
    }

    public function test_array_round_trip_keeps_everything(): void
    {
        $user = new FirebaseUser('u1', 'a@b.c', 'A', null, true, false, ['admin' => true, 'plan' => 'pro'], ['password'], '2026-01-01T00:00:00+00:00');

        $this->assertEquals($user, FirebaseUser::fromArray($user->toArray()));
    }

    public function test_json_output_hides_raw_claims(): void
    {
        $json = (new FirebaseUser('u1', customClaims: ['admin' => true, 'secret' => 'x']))->jsonSerialize();

        $this->assertTrue($json['isAdmin']);
        $this->assertArrayNotHasKey('customClaims', $json);
        $this->assertSame('u1', $json['uid']);
    }
}
