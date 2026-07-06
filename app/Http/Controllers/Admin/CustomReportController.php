<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\CustomReport;
use App\Models\Scholarship;
use App\Models\User;

class CustomReportController extends Controller
{
    /**
     * List all saved reports.
     */
    public function index()
    {
        try {
            $reports = CustomReport::where('user_id', Auth::id())->orderBy('name')->get();
            return Inertia::render('Admin/Reports/Index', [
                'reports' => $reports,
            ]);
        } catch (\Throwable $e) {
            Log::error('CustomReportController@index error: ' . $e->getMessage());
            return Inertia::render('Admin/Reports/Index', ['reports' => []]);
        }
    }

    /**
     * Store a new custom report.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'model'       => 'required|in:Scholarship,User',
                'fields'      => 'required|array|min:1',
                'fields.*'    => 'string',
                'chart_type'  => 'nullable|in:bar,line,table',
                'filters'     => 'nullable|array',
                'aggregation' => 'nullable|array',
                'is_public'   => 'boolean',
            ]);

            $report = CustomReport::create([
                'user_id'     => Auth::id(),
                'name'        => $validated['name'],
                'model'       => $validated['model'],
                'fields'      => $validated['fields'],
                'chart_type'  => $validated['chart_type'] ?? 'table',
                'filters'     => $validated['filters'] ?? [],
                'aggregation' => $validated['aggregation'] ?? [],
                'is_public'   => $validated['is_public'] ?? false,
            ]);

            Log::info('Custom report created.', ['report_id' => $report->id]);

            return redirect()->route('admin.reports.index')->with('success', 'Report saved.');
        } catch (\Throwable $e) {
            Log::error('CustomReportController@store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create report.']);
        }
    }

    /**
     * Update a custom report.
     */
    public function update(Request $request, int $id)
    {
        try {
            $report = CustomReport::where('user_id', Auth::id())->findOrFail($id);
            $report->update($request->only([
                'name', 'fields', 'chart_type', 'filters', 'aggregation', 'is_public',
            ]));

            Log::info('Custom report updated.', ['report_id' => $report->id]);

            return back()->with('success', 'Report updated.');
        } catch (\Throwable $e) {
            Log::error('CustomReportController@update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Update failed.']);
        }
    }

    /**
     * Delete a custom report.
     */
    public function destroy(int $id)
    {
        try {
            $report = CustomReport::where('user_id', Auth::id())->findOrFail($id);
            $report->delete();

            Log::info('Custom report deleted.', ['report_id' => $id]);

            return back()->with('success', 'Report deleted.');
        } catch (\Throwable $e) {
            Log::error('CustomReportController@destroy error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Delete failed.']);
        }
    }

    /**
     * Preview a report configuration or a saved report.
     */
    public function preview(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_id'   => 'nullable|exists:custom_reports,id',
                'model'       => 'required_without:report_id|in:Scholarship,User',
                'fields'      => 'required_without:report_id|array',
                'fields.*'    => 'string',
                'filters'     => 'nullable|array',
                'aggregation' => 'nullable|array',
            ]);

            if (!empty($validated['report_id'])) {
                $report = CustomReport::findOrFail($validated['report_id']);
                $model = $report->model;
                $fields = $report->fields;
                $filters = $report->filters ?? [];
                $aggregation = $report->aggregation ?? [];
            } else {
                $model = $validated['model'];
                $fields = $validated['fields'];
                $filters = $validated['filters'] ?? [];
                $aggregation = $validated['aggregation'] ?? [];
            }

            $query = $model === 'Scholarship' ? Scholarship::query() : User::query();

            // Apply basic filters
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            if (!empty($filters['status']) && $model === 'Scholarship') {
                $query->where('status', $filters['status']);
            }

            // Aggregation
            if (!empty($aggregation['type']) && !empty($aggregation['field'])) {
                $aggField = $aggregation['field'];
                switch ($aggregation['type']) {
                    case 'count':
                        $result = $query->count();
                        break;
                    case 'avg':
                        $result = $query->avg($aggField);
                        break;
                    case 'sum':
                        $result = $query->sum($aggField);
                        break;
                    default:
                        $result = $query->get($fields);
                }
                return response()->json(['data' => $result]);
            }

            // Default: return selected fields
            $data = $query->get($fields);
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            Log::error('CustomReportController@preview error: ' . $e->getMessage());
            return response()->json(['message' => 'Preview failed.'], 500);
        }
    }
}