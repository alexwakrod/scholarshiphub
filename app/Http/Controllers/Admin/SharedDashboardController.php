<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\SharedDashboard;

class SharedDashboardController extends Controller
{
    public function index()
    {
        try {
            $shares = SharedDashboard::where('user_id', Auth::id())->orderBy('name')->get();
            return Inertia::render('Admin/SharedDashboards/Index', [
                'shares' => $shares,
            ]);
        } catch (\Throwable $e) {
            Log::error('SharedDashboardController@index error: ' . $e->getMessage());
            return Inertia::render('Admin/SharedDashboards/Index', ['shares' => []]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'widget_ids' => 'required|array|min:1',
            'widget_ids.*' => 'string',
            'expires_at' => 'nullable|date',
        ]);

        $share = SharedDashboard::create([
            'user_id'    => Auth::id(),
            'name'       => $validated['name'],
            'widget_ids' => $validated['widget_ids'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        Log::info('Shared dashboard created.', ['share_id' => $share->id]);

        return back()->with('success', 'Share link created.');
    }

    public function update(Request $request, int $id)
    {
        $share = SharedDashboard::where('user_id', Auth::id())->findOrFail($id);
        $share->update($request->only(['name', 'widget_ids', 'expires_at']));
        return back()->with('success', 'Share updated.');
    }

    public function destroy(int $id)
    {
        $share = SharedDashboard::where('user_id', Auth::id())->findOrFail($id);
        $share->delete();
        Log::info('Shared dashboard deleted.', ['share_id' => $id]);
        return back()->with('success', 'Share deleted.');
    }
}