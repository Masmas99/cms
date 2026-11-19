<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create or promote the configured admin user.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL dan ADMIN_PASSWORD belum diisi di .env. Seeder admin dilewati.');

            return;
        }

        $user = User::query()->firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->name = config('admin.name', 'Administrator');
            $user->password = $password;
        }

        $user->forceFill([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->command?->info("Admin {$user->email} siap digunakan.");
    }
}
