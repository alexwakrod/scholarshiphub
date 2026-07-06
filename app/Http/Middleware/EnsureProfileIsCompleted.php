<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsCompleted
{
    /**
     * Redirect to profile completion if the user's profile is not 100% complete.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->studentProfile && !$user->studentProfile->profile_completed) {
            // Allow access to the profile completion page itself
            if ($request->routeIs('profile.complete', 'profile.edit', 'profile.update', 'logout')) {
                return $next($request);
            }
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }
}