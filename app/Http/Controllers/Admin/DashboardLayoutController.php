<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DashboardWidget;

class DashboardLayoutController extends Controller
{
    /**
     * Get the current admin dashboard layout.
     */
    public function index()
    {
        try {
            $widgets = DashboardWidget::where('user_id', Auth::id())
                ->orderBy('position_y')
                ->orderBy('position_x')
                ->get()
                ->map(function ($w) {
                    return [
                        'widget_id' => $w->widget_id,
                        'title' => $w->title,
                        'config' => $w->config,
                        'width' => $w->width,
                        'height' => $w->height,
                        'visible' => $w->visible,
                    ];
                });

            return response()->json($widgets);
        } catch (\Throwable $e) {
            Log::error('Admin\DashboardLayoutController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([], 500);
        }
    }

    /**
     * Save/replace the dashboard layout.
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'widgets' => 'required|array',
                'widgets.*.widget_id' => 'required|string',
                'widgets.*.width' => 'required|integer|min:1|max:4',
                'widgets.*.height' => 'required|integer|min:1|max:4',
                'widgets.*.visible' => 'required|boolean',
            ]);

            $userId = Auth::id();

            // Remove old layout for this admin
            DashboardWidget::where('user_id', $userId)->delete();

            foreach ($request->widgets as $index => $widgetData) {
                DashboardWidget::create([
                    'user_id' => $userId,
                    'widget_id' => $widgetData['widget_id'],
                    'title' => $widgetData['title'] ?? null,
                    'config' => $widgetData['config'] ?? null,
                    'position_x' => 0,
                    'position_y' => $index,
                    'width' => $widgetData['width'],
                    'height' => $widgetData['height'],
                    'visible' => $widgetData['visible'],
                ]);
            }

            Log::info('Admin dashboard layout saved.', ['user_id' => $userId]);

            return response()->json(['message' => 'Layout saved.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin\DashboardLayoutController@save error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Save failed.'], 500);
        }
    }
}