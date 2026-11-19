<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_under_construction_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Rumitech')
            ->assertSee('Under Construction')
            ->assertSee('Currently under construction')
            ->assertSee('images/logo.webp')
            ->assertSee('Masuk ke aplikasi')
            ->assertSee(route('login'), false);
    }

    public function test_landing_page_exposes_open_graph_metadata_for_sharing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:image"', false)
            ->assertSee(asset('images/banner.webp'), false)
            ->assertSee('property="og:image:width" content="1024"', false)
            ->assertSee('property="og:image:height" content="572"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    public function test_open_graph_image_uses_the_current_request_host(): void
    {
        $this->get('http://20.92.125.158.sslip.io:8088/')
            ->assertOk()
            ->assertSee('http://20.92.125.158.sslip.io:8088/images/banner.webp', false);
    }

    public function test_authenticated_users_also_see_the_under_construction_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Under Construction');
    }

    public function test_authenticated_users_can_view_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/ess')
            ->assertOk()
            ->assertSee('Rumitech')
            ->assertSee('Dashboard');
    }
}
