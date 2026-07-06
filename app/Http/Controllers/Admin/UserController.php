<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\User;

class UserController extends Controller
{
    /**
     * List all registered users with search and pagination.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query()->with('studentProfile');

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            }

            if ($role = $request->input('role')) {
                $query->where('role', $role);
            }

            $sortField = $request->input('sort', 'created_at');
            $sortDir = $request->input('direction', 'desc');
            $allowedSorts = ['id', 'name', 'email', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $users = $query->paginate(25)->withQueryString();

            return Inertia::render('Admin/Users/Index', [
                'users' => $users,
                'filters' => $request->only(['search', 'role', 'sort', 'direction']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\UserController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Users/Index', ['users' => [], 'filters' => []])
                ->with('error', 'Failed to load users.');
        }
    }

    /**
     * Show individual user details (profile, applications, documents).
     */
    public function show(int $id)
    {
        try {
            $user = User::with(['studentProfile', 'documents', 'applications.scholarship'])->findOrFail($id);

            return Inertia::render('Admin/Users/Show', [
                'user' => $user->load('studentProfile'),
                'documents' => $user->documents,
                'applications' => $user->applications,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Admin user show not found: ' . $id);
            return redirect()->route('admin.users.index')->with('error', 'User not found.');
        } catch (\Throwable $e) {
            Log::error('Admin\UserController@show error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.users.index')->with('error', 'Failed to load user.');
        }
    }
}