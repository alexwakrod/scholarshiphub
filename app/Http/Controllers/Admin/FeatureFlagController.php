<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class FeatureFlagController extends Controller
{
    private string $cacheKey = 'feature_flags';

    /**
     * Display feature flags management page.
     */
    public function index()
    {
        try {
            $flags = Cache::get($this->cacheKey, $this->getDefaultFlags());
            return Inertia::render('Admin/FeatureFlags/Index', [
                'flags' => $flags,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\FeatureFlagController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/FeatureFlags/Index', ['flags' => $this->getDefaultFlags()]);
        }
    }

    /**
     * Toggle a feature flag.
     */
    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'feature' => 'required|string',
                'enabled' => 'required|boolean',
            ]);

            $flags = Cache::get($this->cacheKey, $this->getDefaultFlags());
            $flags[$request->feature] = $request->enabled;
            Cache::forever($this->cacheKey, $flags);

            Log::info('Feature flag toggled.', [
                'feature' => $request->feature,
                'enabled' => $request->enabled,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json(['message' => 'Flag updated.', 'flags' => $flags]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin\FeatureFlagController@toggle error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update flag.'], 500);
        }
    }

    /**
     * Default feature flags if cache is empty.
     */
    private function getDefaultFlags(): array
    {
        return [
            'ai_document_review' => true,
            'pathway_generator' => true,
            'community_reviews' => true,
            'import_scholarships' => true,
            'survey' => false,
        ];
    }
}