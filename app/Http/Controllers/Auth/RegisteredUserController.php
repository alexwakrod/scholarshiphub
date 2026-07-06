<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): \Inertia\Response
    {
        try {
            return Inertia::render('Auth/Register');
        } catch (\Throwable $e) {
            Log::error('RegisteredUserController@create error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Auth/Register');
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'avatar' => ['nullable', 'image', 'max:2048'],
            ]);

            $avatarUrl = null;
            if ($request->hasFile('avatar')) {
                try {
                    $avatarUrl = $request->file('avatar')->store('avatars', 'public');
                } catch (\Throwable $e) {
                    Log::warning('Avatar upload failed, continuing without: ' . $e->getMessage());
                }
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'avatar_url' => $avatarUrl,
                'locale' => $request->getPreferredLanguage(['en', 'ar']) ?? 'en',
                'role' => 'student',
            ]);

            event(new Registered($user));

            Auth::login($user);

            $user->studentProfile()->create([
                'full_name'         => $request->name,
                'profile_completed' => false,
            ]);
            Log::info('New user registered.', ['user_id' => $user->id]);
            return redirect()->route('profile.complete');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('RegisteredUserController@store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['email' => 'Registration failed. Please try again later.']);
        }
    }
}