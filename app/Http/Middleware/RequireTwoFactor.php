<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactor
{
    /**
     * If user does not have 2FA enabled, redirect to setup.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->two_factor_enabled) {
            return redirect()->route('auth.2fa.setup')
                ->with('warning', 'Please set up two‑factor authentication to continue.');
        }

        return $next($request);
    }
}