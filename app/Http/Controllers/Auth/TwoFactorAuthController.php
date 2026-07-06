<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FALaravel\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthController extends Controller
{
    /**
     * Show 2FA setup wizard step 1: enable and show QR code.
     */
    public function setup(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->two_factor_secret) {
                return inertia('Auth/TwoFactor/Status', [
                    'enabled' => true,
                ]);
            }

            $google2fa = app(Google2FA::class);
            $secret = $google2fa->generateSecretKey();
            session()->put('two_factor_temp_secret', $secret);

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );

            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCodeUrl);

            return inertia('Auth/TwoFactor/Setup', [
                'secret' => $secret,
                'qrCodeSvg' => $qrCodeSvg,
            ]);
        } catch (\Throwable $e) {
            Log::error('TwoFactorAuthController@setup error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard')->with('error', 'Failed to initialize 2FA setup.');
        }
    }

    /**
     * Verify and enable 2FA.
     */
    public function enable(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6',
            ]);

            $user = Auth::user();
            $secret = session()->get('two_factor_temp_secret');

            if (!$secret) {
                return back()->withErrors(['code' => 'Session expired. Please start again.']);
            }

            $google2fa = app(Google2FA::class);
            $valid = $google2fa->verifyKey($secret, $request->code);

            if (!$valid) {
                return back()->withErrors(['code' => 'Invalid verification code.']);
            }

            $user->two_factor_secret = $secret;
            $user->two_factor_enabled = true;
            $user->save();

            session()->forget('two_factor_temp_secret');

            Log::info('2FA enabled for user.', ['user_id' => $user->id]);

            return redirect()->route('auth.2fa.status')->with('success', 'Two‑factor authentication has been enabled.');
        } catch (\Throwable $e) {
            Log::error('TwoFactorAuthController@enable error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['code' => 'Verification failed.']);
        }
    }

    /**
     * Disable 2FA (with confirmation).
     */
    public function disable(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6',
            ]);

            $user = Auth::user();
            $google2fa = app(Google2FA::class);

            if (!$google2fa->verifyKey($user->two_factor_secret, $request->code)) {
                return back()->withErrors(['code' => 'Invalid verification code.']);
            }

            $user->two_factor_secret = null;
            $user->two_factor_enabled = false;
            $user->save();

            Log::info('2FA disabled for user.', ['user_id' => $user->id]);

            return redirect()->route('dashboard')->with('success', 'Two‑factor authentication has been disabled.');
        } catch (\Throwable $e) {
            Log::error('TwoFactorAuthController@disable error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['code' => 'Failed to disable 2FA.']);
        }
    }

    /**
     * Show 2FA status (enabled/disabled).
     */
    public function status()
    {
        $user = Auth::user();
        return inertia('Auth/TwoFactor/Status', [
            'enabled' => $user->two_factor_enabled,
        ]);
    }

    /**
     * Show 2FA verification challenge during login.
     */
    public function challenge()
    {
        if (!session()->has('two_factor_auth:user_id')) {
            return redirect()->route('login');
        }

        return inertia('Auth/TwoFactor/Challenge');
    }

    /**
     * Verify the 2FA code during login.
     */
    public function verifyChallenge(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6',
            ]);

            $userId = session()->get('two_factor_auth:user_id');
            if (!$userId) {
                return redirect()->route('login');
            }

            $user = \App\Models\User::findOrFail($userId);
            $google2fa = app(Google2FA::class);

            if (!$google2fa->verifyKey($user->two_factor_secret, $request->code)) {
                return back()->withErrors(['code' => 'Invalid code.']);
            }

            Auth::login($user);
            session()->forget('two_factor_auth:user_id');

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Throwable $e) {
            Log::error('TwoFactorAuthController@verifyChallenge error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['code' => 'Verification failed.']);
        }
    }
}