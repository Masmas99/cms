<?php

namespace Tests\Feature;

use Database\Seeders\DummyUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DummyUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_dummy_users_without_duplicates_on_repeated_runs(): void
    {
        $this->seed(DummyUserSeeder::class);

        $this->assertDatabaseCount('users', 1000);
        $this->assertDatabaseHas('users', ['email' => 'dummy1@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'dummy1000@example.com']);

        $this->seed(DummyUserSeeder::class);

        $this->assertDatabaseCount('users', 1000);
    }
}
