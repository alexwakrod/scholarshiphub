<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    public function redirect(string $provider)
    {
        try {
            $validProviders = ['github', 'google'];
            if (!in_array($provider, $validProviders)) {
                abort(404);
            }
            return Socialite::driver($provider)->redirect();
        } catch (\Throwable $e) {
            Log::error('Socialite redirect error: ' . $e->getMessage(), ['provider' => $provider]);
            return redirect()->route('login')->with('error', 'Unable to connect with ' . ucfirst($provider) . '.');
        }
    }

    public function callback(string $provider)
    {
        try {
            $validProviders = ['github', 'google'];
            if (!in_array($provider, $validProviders)) {
                abort(404);
            }

            $socialUser = Socialite::driver($provider)->user();

            $user = User::where('email', $socialUser->getEmail())->first();
            if (!$user) {
                $user = User::create([
                    'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email'             => $socialUser->getEmail(),
                    'password'          => bcrypt(Str::random(32)),
                    'avatar_url'        => $socialUser->getAvatar(),
                    'role'              => 'student',
                    'locale'            => 'en',
                    'email_verified_at' => now(),
                ]);

                // Create empty student profile for the new user
                $user->studentProfile()->create([
                    'full_name'         => $user->name,
                    'profile_completed' => false,
                ]);
            } else {
                // Existing user: ensure email is marked as verified
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }

                // Create profile if it doesn't exist yet
                if (!$user->studentProfile) {
                    $user->studentProfile()->create([
                        'full_name'         => $user->name,
                        'profile_completed' => false,
                    ]);
                }
            }

            Auth::login($user, true);
            Log::info('Social login success.', ['user_id' => $user->id, 'provider' => $provider]);

            // Redirect to profile completion (middleware will handle if already completed)
            return redirect()->route('profile.complete');
        } catch (\Throwable $e) {
            Log::error('Socialite callback error: ' . $e->getMessage(), ['provider' => $provider]);
            return redirect()->route('login')->with('error', 'Authentication with ' . ucfirst($provider) . ' failed.');
        }
    }
}