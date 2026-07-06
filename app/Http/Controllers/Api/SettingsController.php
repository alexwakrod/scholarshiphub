<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Settings/Index', [
            'email'            => $user->email,
            'twoFactorEnabled' => $user->two_factor_enabled ?? false,
            'pendingDeletion'  => $user->deletion_scheduled_at
                ? $user->deletion_scheduled_at->toISOString()
                : null,
        ]);
    }

    /**
     * Send a verification code to the new email address.
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $code = random_int(100000, 999999);
        $newEmail = $request->new_email;

        // Store the code and new email in cache for 15 minutes
        Cache::put('email_change_code:' . $user->id, [
            'code'      => $code,
            'new_email' => $newEmail,
        ], now()->addMinutes(15));

        // Send verification email
        try {
            Mail::raw("Your verification code for changing your email address is: {$code}", function ($message) use ($newEmail) {
                $message->to($newEmail)
                        ->subject('Verify your new email address');
            });
            Log::info('Email change verification code sent.', [
                'user_id'   => $user->id,
                'new_email' => $newEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send verification email. Please try again.');
        }

        return back()->with('success', 'A verification code has been sent to ' . $newEmail . '. Please enter it below.');
    }

    /**
     * Verify the code and update the user's email.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $cacheKey = 'email_change_code:' . $user->id;
        $data = Cache::get($cacheKey);

        if (!$data) {
            return back()->withErrors(['code' => 'Verification code expired or not found.']);
        }

        if ((int) $request->code !== $data['code']) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        $oldEmail = $user->email;
        $user->email = $data['new_email'];
        $user->save();

        // Clear the cache
        Cache::forget($cacheKey);

        Log::info('User email updated via verification.', [
            'user_id'   => $user->id,
            'old_email' => $oldEmail,
            'new_email' => $user->email,
        ]);

        return redirect()->route('settings')->with('success', 'Email address updated successfully.');
    }

    public function scheduleDeletion()
    {
        $user = Auth::user();
        $user->deletion_scheduled_at = now()->addDays(7);
        $user->save();

        Log::info('User account deletion scheduled.', [
            'user_id'                => $user->id,
            'deletion_scheduled_at'  => $user->deletion_scheduled_at->toISOString(),
        ]);

        return back()->with('success', 'Your account will be deleted in 7 days. You can cancel this action at any time before then.');
    }

    public function cancelDeletion()
    {
        $user = Auth::user();
        $user->deletion_scheduled_at = null;
        $user->save();

        Log::info('User account deletion cancelled.', ['user_id' => $user->id]);

        return back()->with('success', 'Account deletion has been cancelled.');
    }
}