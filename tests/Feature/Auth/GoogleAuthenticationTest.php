<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
        ]);
    }

    public function test_users_are_redirected_to_google(): void
    {
        Socialite::fake('google');

        $this->get(route('auth.google'))->assertRedirect();
    }

    public function test_google_login_requires_credentials_to_be_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('auth.google'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('popup', fn (array $popup): bool => $popup['title'] === 'Login Google belum aktif');
    }

    public function test_new_users_are_registered_automatically_with_google(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'Mashudi',
            'email' => 'mashudi@example.com',
            'avatar' => 'https://example.com/avatar.png',
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Mashudi',
            'email' => 'mashudi@example.com',
            'google_id' => 'google-123',
            'avatar' => 'https://example.com/avatar.png',
        ]);

        $this->assertNotNull(User::where('email', 'mashudi@example.com')->first()->email_verified_at);
    }

    public function test_existing_users_can_login_with_google(): void
    {
        $user = User::factory()->create([
            'email' => 'mashudi@example.com',
            'google_id' => 'google-123',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'name' => $user->name,
            'email' => $user->email,
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_accounts_are_linked_by_email(): void
    {
        $user = User::factory()->create(['email' => 'mashudi@example.com']);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-456',
            'name' => $user->name,
            'email' => $user->email,
        ]));

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-456',
        ]);
    }
}
