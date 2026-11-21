<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:make-admin {email}', function (string $email) {
    $user = User::query()->where('email', $email)->first();

    if ($user === null) {
        $this->error("User dengan email {$email} tidak ditemukan.");

        return 1;
    }

    $user->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->info("{$user->email} sekarang memiliki role admin.");

    return 0;
})->purpose('Promote a user to admin');

Schedule::command('audit:prune')
    ->dailyAt('02:00')
    ->withoutOverlapping();
