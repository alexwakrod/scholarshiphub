<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ActivityHeatmapController extends Controller
{
    /**
     * Return heatmap data for the given year (default current year).
     * Returns an array of { date: 'YYYY-MM-DD', count: int }
     */
    public function __invoke(Request $request)
    {
        try {
            $year = $request->integer('year', now()->year);

            $logs = ActivityLog::selectRaw("DATE(created_at) as date, COUNT(*) as count")
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => [
                    'date'  => $row->date,
                    'count' => $row->count,
                ]);

            return response()->json($logs);
        } catch (\Throwable $e) {
            Log::error('ActivityHeatmapController error: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}