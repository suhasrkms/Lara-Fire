<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_profile_update_only_targets_the_signed_in_user(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->expects('updateUser')->with('uid-123', ['displayName' => 'New Name'])->andReturn($this->userRecord());

        $this->actingAs($this->firebaseUser())
            ->patch('/profile', ['displayName' => 'New Name', 'email' => 'jane@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_changing_email_resets_verification(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->expects('updateUser')->with('uid-123', [
            'displayName' => 'Jane Doe',
            'email' => 'new@example.com',
            'emailVerified' => false,
        ])->andReturn($this->userRecord());
        $auth->expects('sendEmailVerificationLink')->with('new@example.com');

        $this->actingAs($this->firebaseUser())
            ->patch('/profile', ['displayName' => 'Jane Doe', 'email' => 'new@example.com'])
            ->assertSessionHas('status');
    }

    public function test_disabling_account_uses_own_uid_and_logs_out(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->expects('disableUser')->with('uid-123')->andReturn($this->userRecord(['disabled' => true]));
        $auth->expects('revokeRefreshTokens')->with('uid-123');

        $this->actingAs($this->firebaseUser())->delete('/profile')->assertRedirect('/');
        $this->assertGuest();
    }
}
