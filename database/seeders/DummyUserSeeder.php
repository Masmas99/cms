<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    /**
     * Seed 1.000 dummy users.
     */
    public function run(): void
    {
        User::factory()
            ->count(1000)
            ->create();

        $this->command?->info('1.000 dummy user berhasil dibuat.');
    }
}
