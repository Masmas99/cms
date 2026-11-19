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
            ->assertSee('images/banner.webp')
            ->assertSee('http://20.92.125.158.sslip.io:8088/', false);
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
