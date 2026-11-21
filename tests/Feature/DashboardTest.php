<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_the_sidebar_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/ess')
            ->assertOk()
            ->assertViewIs('pages.ess.dashboard')
            ->assertSee('Buka menu')
            ->assertSee('Menu utama')
            ->assertSee('Dashboard')
            ->assertSee('Operasional gudang')
            ->assertSee('Penyesuaian')
            ->assertSee(route('ess.wms.adjustments.index'), false)
            ->assertDontSee('Master data')
            ->assertSee('Profil')
            ->assertSee('Keluar')
            ->assertSee('images/logo.webp');
    }

    public function test_legacy_dashboard_url_redirects_to_the_ess_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/ess');
    }

    public function test_old_pages_ess_url_redirects_to_the_ess_page(): void
    {
        $this->get('/pages/ess')->assertRedirect('/ess');
    }

    public function test_dashboard_limits_warehouse_occupancy_to_eight_rows(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 10) as $index) {
            Warehouse::factory()->create(['name' => sprintf('Gudang Uji %02d', $index)]);
        }

        $this->actingAs($user)
            ->get('/ess')
            ->assertOk()
            ->assertSee('Okupansi gudang')
            ->assertSee('Gudang Uji 01')
            ->assertSee('Gudang Uji 08')
            ->assertDontSee('Gudang Uji 09')
            ->assertDontSee('Gudang Uji 10')
            ->assertSee(route('ess.wms.warehouses.index'), false);
    }

    public function test_dashboard_renders_the_goods_flow_chart_for_each_period(): void
    {
        $user = User::factory()->create();

        foreach ([7 => 'max-w-[2rem]', 30 => 'max-w-[0.75rem]', 90 => 'max-w-[0.25rem]'] as $days => $barWidthClass) {
            $this->actingAs($user)
                ->get('/ess?days='.$days)
                ->assertOk()
                ->assertViewIs('pages.ess.dashboard')
                ->assertSee('Arus barang')
                ->assertSee('chart-bar', false)
                ->assertSee($barWidthClass, false);
        }
    }

    public function test_dashboard_shows_a_welcome_toast_for_new_users(): void
    {
        $user = User::factory()->create(['name' => 'Mashudi']);

        $this->actingAs($user)
            ->withSession(['welcome' => 'register'])
            ->get('/ess')
            ->assertOk()
            ->assertSee('Selamat datang, Mashudi!')
            ->assertSee('fixed right-4 top-20', false)
            ->assertSee('lg:top-4', false)
            ->assertSee('role="status"', false);
    }

    public function test_dashboard_shows_a_welcome_back_toast_for_returning_users(): void
    {
        $user = User::factory()->create(['name' => 'Mashudi']);

        $this->actingAs($user)
            ->withSession(['welcome' => 'login'])
            ->get('/ess')
            ->assertOk()
            ->assertSee('Selamat datang kembali, Mashudi!')
            ->assertSee('role="status"', false);
    }

    public function test_welcome_toast_appears_once_after_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Mashudi',
            'email' => 'mashudi@example.com',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHas('welcome', 'login');

        $this->get('/ess')
            ->assertOk()
            ->assertSee('Selamat datang kembali, Mashudi!')
            ->assertSee('Tutup notifikasi');

        $this->get('/ess')
            ->assertOk()
            ->assertDontSee('Selamat datang kembali, Mashudi!')
            ->assertDontSee('Tutup notifikasi');
    }
}
