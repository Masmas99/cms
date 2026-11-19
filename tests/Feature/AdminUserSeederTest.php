<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_admin_user(): void
    {
        config([
            'admin.name' => 'Mashudi',
            'admin.email' => 'admin@example.test',
            'admin.password' => 'secret-password',
        ]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertSame('Mashudi', $admin->name);
        $this->assertTrue($admin->isAdmin());
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('secret-password', $admin->password));
    }

    public function test_it_promotes_an_existing_user_without_changing_their_password(): void
    {
        $user = User::factory()->unverified()->create([
            'name' => 'Existing User',
            'email' => 'admin@example.test',
            'password' => 'existing-password',
        ]);

        config([
            'admin.name' => 'Mashudi',
            'admin.email' => 'admin@example.test',
            'admin.password' => 'new-password',
        ]);

        $this->seed(AdminUserSeeder::class);

        $user->refresh();

        $this->assertSame('Existing User', $user->name);
        $this->assertTrue($user->isAdmin());
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('existing-password', $user->password));
    }

    public function test_database_seeder_creates_the_configured_admin_user(): void
    {
        config([
            'admin.name' => 'Mashudi',
            'admin.email' => 'admin@example.test',
            'admin.password' => 'secret-password',
        ]);

        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_it_skips_when_admin_credentials_are_missing(): void
    {
        config([
            'admin.email' => null,
            'admin.password' => null,
        ]);

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 0);
    }
}
