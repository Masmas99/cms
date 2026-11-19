<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_access_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_dashboard_uses_the_same_summary_card_as_the_user_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Mashudi']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Selamat datang kembali, Mashudi.')
            ->assertSee('Ringkasan')
            ->assertSee('Anda berhasil masuk')
            ->assertSee('Gunakan menu di sebelah kiri untuk mengakses dashboard dan pengaturan profil Anda.');
    }

    public function test_regular_users_cannot_access_the_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_filament_panel_url_is_not_available(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/panel')
            ->assertNotFound();
    }

    public function test_guests_are_redirected_to_the_shared_login_page(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('login'));
    }

    public function test_admin_login_url_redirects_to_the_shared_login_page(): void
    {
        $this->get('/admin/login')
            ->assertRedirect(route('login'));
    }

    public function test_admins_can_log_in_from_the_shared_login_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('welcome', 'login');

        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk();
    }

    public function test_regular_users_are_not_sent_to_the_admin_panel_after_login(): void
    {
        $user = User::factory()->create();

        $this->get('/admin')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_dashboard_shows_the_admin_panel_link(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Panel admin')
            ->assertSee('Manajemen user')
            ->assertSee(route('admin.dashboard'), false)
            ->assertSee(route('admin.users.index'), false)
            ->assertDontSee(route('dashboard'), false);
    }

    public function test_user_dashboard_shows_the_user_dashboard_link(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/ess')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('dashboard'), false)
            ->assertDontSee('Panel admin')
            ->assertDontSee('Manajemen user');
    }

    public function test_admins_are_redirected_away_from_the_user_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/ess')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_be_promoted_to_admin(): void
    {
        $user = User::factory()->create();

        $this->artisan('user:make-admin', ['email' => $user->email])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }
}
