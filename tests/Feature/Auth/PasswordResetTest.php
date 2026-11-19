<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);

        $response
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'Link reset kata sandi sudah dikirim. Silakan cek email Anda.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_request_rejects_an_unknown_email(): void
    {
        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'unknown@example.test',
        ]);

        $response
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors([
                'email' => 'Email tersebut tidak terdaftar.',
            ]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_reset_email_is_written_in_indonesian(): void
    {
        Notification::fake();

        app()->setLocale('id');

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->assertSame('id', $notification->locale);

            $mail = $notification->toMail($user);

            $this->assertSame('Reset kata sandi Anda', $mail->subject);
            $this->assertSame('Reset Kata Sandi', $mail->actionText);
            $this->assertContains('Tautan reset kata sandi ini akan kedaluwarsa dalam 60 menit.', $mail->outroLines);

            $html = $mail->render();

            $this->assertStringContainsString('Halo!', $html);
            $this->assertStringContainsString('Salam,', $html);
            $this->assertStringContainsString('Hak cipta dilindungi.', $html);
            $this->assertStringContainsString('Jika Anda mengalami kesulitan mengklik tombol', $html);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertSessionHas('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.')
                ->assertRedirect(route('login'));

            return true;
        });
    }
}
