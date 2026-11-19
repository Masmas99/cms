<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_has_password_visibility_toggles(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), "x-bind:type=\"visible ? 'text' : 'password'\""));
    }

    public function test_registration_screen_links_to_the_google_consent_flow(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Daftar dengan Google')
            ->assertSee('href="'.route('auth.google.register').'"', false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
