<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Mail\VerificationCodeMail;

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
     * Send a 6‑digit verification code to the new email address.
     *
     * Called via AJAX – returns JSON.
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'new_email' => [
                'required',
                'email',
                'unique:users,email,' . Auth::id(),      // reject if taken by another user
            ],
        ], [
            'new_email.unique' => 'This email address is already linked to another account.',
        ]);

        $user     = Auth::user();
        $newEmail = $request->new_email;
        $code     = random_int(100000, 999999);

        // Save the code and target email in cache for 15 minutes
        Cache::put('email_change_code:' . $user->id, [
            'code'      => $code,
            'new_email' => $newEmail,
        ], now()->addMinutes(15));

        try {
            Mail::to($newEmail)->send(new VerificationCodeMail($code, $newEmail));

            Log::info('Email change verification code sent.', [
                'user_id'   => $user->id,
                'new_email' => $newEmail,
            ]);

            return response()->json([
                'message' => 'A verification code has been sent to ' . $newEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage(), [
                'user_id'   => $user->id,
                'new_email' => $newEmail,
            ]);

            return response()->json([
                'message' => 'Failed to send verification email. Please check your email configuration and try again.',
            ], 500);
        }
    }

    /**
     * Verify the 6‑digit code and update the user's email address.
     *
     * Called via AJAX – returns JSON.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user     = Auth::user();
        $cacheKey = 'email_change_code:' . $user->id;
        $data     = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'message' => 'Verification code expired or not found.',
            ], 422);
        }

        if ((int) $request->code !== $data['code']) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $oldEmail = $user->email;
        $user->email = $data['new_email'];
        $user->save();

        Cache::forget($cacheKey);

        Log::info('User email updated via verification.', [
            'user_id'   => $user->id,
            'old_email' => $oldEmail,
            'new_email' => $user->email,
        ]);

        return response()->json([
            'message' => 'Email address updated successfully.',
        ]);
    }

    /**
     * Schedule account deletion in 7 days.
     */
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

    /**
     * Cancel the pending account deletion.
     */
    public function cancelDeletion()
    {
        $user = Auth::user();
        $user->deletion_scheduled_at = null;
        $user->save();

        Log::info('User account deletion cancelled.', ['user_id' => $user->id]);

        return back()->with('success', 'Account deletion has been cancelled.');
    }
}