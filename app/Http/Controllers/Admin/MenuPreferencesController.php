<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MenuPreferencesController extends Controller
{
    /**
     * Get the current user's menu preferences.
     */
    public function show()
    {
        try {
            $user = Auth::user();
            $prefs = $user->menu_preferences;
            return response()->json($prefs ?? []);
        } catch (\Throwable $e) {
            Log::error('MenuPreferencesController@show error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Save the user's menu preferences.
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.route' => 'required|string',
                'items.*.label' => 'required|string',
                'items.*.visible' => 'required|boolean',
            ]);

            $user = Auth::user();
            $user->menu_preferences = $validated['items'];
            $user->save();

            Log::info('Menu preferences saved.', ['user_id' => $user->id]);

            return response()->json(['message' => 'Preferences saved.']);
        } catch (\Throwable $e) {
            Log::error('MenuPreferencesController@update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Save failed.'], 500);
        }
    }
}