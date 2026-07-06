<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create(): \Inertia\Response
    {
        try {
            return Inertia::render('Auth/Login', [
                'canResetPassword' => true,
                'status' => session('status'),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuthenticatedSessionController@create error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Auth/Login', [
                'canResetPassword' => true,
                'status' => session('status'),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (!Auth::attempt($credentials, $request->boolean('remember'))) {
                Log::warning('Failed login attempt for email: ' . $request->input('email'));
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            $user = Auth::user();

            // If 2FA is enabled, log out temporarily and redirect to 2FA challenge
            if ($user->two_factor_enabled) {
                $userId = $user->id;
                Auth::logout();
                session()->put('two_factor_auth:user_id', $userId);
                return redirect()->route('auth.2fa.challenge');
            }

            $request->session()->regenerate();

            Log::info('User logged in.', ['user_id' => $user->id]);

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('AuthenticatedSessionController@store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['email' => 'An unexpected error occurred. Please try again.']);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $userId = Auth::id();
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Log::info('User logged out.', ['user_id' => $userId]);
            return redirect('/');
        } catch (\Throwable $e) {
            Log::error('AuthenticatedSessionController@destroy error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect('/');
        }
    }
}