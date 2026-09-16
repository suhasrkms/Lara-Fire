<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Firebase');
        $this->get('/login')->assertOk()->assertSee('Welcome back');
        $this->get('/register')->assertOk()->assertSee('Create your account');
        $this->get('/forgot-password')->assertOk();
        $this->get('/up')->assertOk();
    }

    public function test_service_worker_is_javascript(): void
    {
        config(['larafire.web.apiKey' => 'test-key', 'larafire.web.messagingSenderId' => '123']);

        $this->get('/firebase-messaging-sw.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript')
            ->assertSee('"apiKey":"test-key"', false);
    }

    public function test_protected_pages_redirect_guests(): void
    {
        foreach (['/home', '/profile', '/notes', '/admin'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }

    public function test_old_self_promote_route_is_gone(): void
    {
        $this->actingAs($this->firebaseUser())->get('/home/iamadmin')->assertNotFound();
    }
}
