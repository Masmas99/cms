<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): RedirectResponse|SymfonyRedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()
                ->route('login')
                ->with('popup', [
                    'type' => 'error',
                    'title' => 'Login Google belum aktif',
                    'message' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env.',
                ]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Authenticate the user using the Google account returned by the callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('login')
                ->with('popup', [
                    'type' => 'error',
                    'title' => 'Login Google gagal',
                    'message' => 'Terjadi kesalahan saat menghubungi Google. Silakan coba lagi.',
                ]);
        }

        $email = $googleUser->getEmail();

        if (blank($email)) {
            return redirect()
                ->route('login')
                ->with('popup', [
                    'type' => 'error',
                    'title' => 'Login Google gagal',
                    'message' => 'Akun Google Anda tidak memiliki alamat email.',
                ]);
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first()
            ?? User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::password(32)),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $user->avatar ?? $googleUser->getAvatar(),
            ])->save();
        }

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
