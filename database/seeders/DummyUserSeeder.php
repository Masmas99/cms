<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    /**
     * Seed 1.000 dummy users without creating duplicate accounts.
     */
    public function run(): void
    {
        $existingEmails = User::query()
            ->where('email', 'like', 'dummy%@example.com')
            ->pluck('email')
            ->flip();

        $created = 0;

        for ($index = 1; $index <= 1000; $index++) {
            $email = "dummy{$index}@example.com";

            if ($existingEmails->has($email)) {
                continue;
            }

            User::factory()->create(['email' => $email]);
            $created++;
        }

        $this->command?->info("{$created} dummy user baru berhasil dibuat.");
    }
}
