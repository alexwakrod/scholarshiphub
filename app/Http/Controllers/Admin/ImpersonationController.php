<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ImpersonationController extends Controller
{
    public function start(int $userId)
    {
        try {
            $admin = Auth::user();
            $target = User::findOrFail($userId);

            if ($target->id === $admin->id) {
                return back()->with('error', 'You cannot impersonate yourself.');
            }

            session()->put('impersonator_id', $admin->id);
            Auth::login($target);

            Log::info('Admin impersonation started.', [
                'admin_id' => $admin->id,
                'target_id' => $target->id,
            ]);

            return redirect()->route('dashboard')->with('success', 'You are now logged in as ' . $target->name . '.');
        } catch (\Throwable $e) {
            Log::error('Impersonation start error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to impersonate user.');
        }
    }

    public function stop()
    {
        try {
            $impersonatorId = session()->pull('impersonator_id');
            if ($impersonatorId) {
                Auth::loginUsingId($impersonatorId);
                Log::info('Admin impersonation stopped.', ['admin_id' => $impersonatorId]);
                return redirect()->route('admin.users.index')->with('success', 'You are back to your admin account.');
            }
            return redirect()->route('dashboard');
        } catch (\Throwable $e) {
            Log::error('Impersonation stop error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard')->with('error', 'Failed to stop impersonation.');
        }
    }
}