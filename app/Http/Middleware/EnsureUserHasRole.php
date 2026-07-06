<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login');
            }
            if (!in_array($user->role->value, $roles)) {
                Log::warning('Unauthorized role access.', [
                    'user_id' => $user->id,
                    'role' => $user->role->value,
                    'required_roles' => $roles,
                    'path' => $request->path(),
                ]);
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        } catch (\Throwable $e) {
            Log::error('EnsureUserHasRole middleware error: ' . $e->getMessage());
            abort(500, 'Internal server error.');
        }
    }
}