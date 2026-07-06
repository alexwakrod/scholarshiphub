<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureNotImpersonating
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('impersonator_id')) {
            abort(403, 'You cannot perform this action while impersonating.');
        }
        return $next($request);
    }
}