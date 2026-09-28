<?php

namespace Tests\Feature;

use ArrayIterator;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function test_non_admin_cannot_open_admin_panel(): void
    {
        $this->actingAs($this->firebaseUser())
            ->get('/admin')
            ->assertRedirect(route('home'));
    }

    public function test_admin_sees_user_list(): void
    {
        $this->mockFirebaseAuth()->allows('listUsers')->andReturn(new ArrayIterator([
            $this->userRecord(['claims' => ['admin' => true]]),
            $this->userRecord(['localId' => 'uid-2', 'email' => 'bob@example.com', 'displayName' => 'Bob']),
        ]));

        $this->actingAs($this->firebaseUser(['claims' => ['admin' => true]]))
            ->get('/admin')
            ->assertOk()
            ->assertSee('bob@example.com');
    }

    public function test_admin_can_grant_admin_role_preserving_other_claims(): void
    {
        $auth = $this->mockFirebaseAuth();
        $auth->allows('getUser')->with('uid-2')->andReturn($this->userRecord(['localId' => 'uid-2', 'claims' => ['plan' => 'pro']]));
        $auth->expects('setCustomUserClaims')->with('uid-2', ['plan' => 'pro', 'admin' => true]);

        $this->actingAs($this->firebaseUser(['claims' => ['admin' => true]]))
            ->post('/admin/users/uid-2/toggle-admin')
            ->assertSessionHas('status', 'Admin role granted.');
    }

    public function test_admin_cannot_demote_or_disable_themselves(): void
    {
        $this->mockFirebaseAuth();
        $admin = $this->firebaseUser(['claims' => ['admin' => true]]);

        $this->actingAs($admin)->post('/admin/users/uid-123/toggle-admin')->assertStatus(422);
        $this->actingAs($admin)->post('/admin/users/uid-123/toggle-disabled')->assertStatus(422);
    }
}
