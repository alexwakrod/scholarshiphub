<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\ActivityLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ActivityLog::with('user:id,name,email')->latest('created_at');

            if ($userId = $request->input('user_id')) {
                $query->where('user_id', $userId);
            }
            if ($action = $request->input('action')) {
                $query->where('action', $action);
            }
            if ($subject = $request->input('subject_type')) {
                $query->where('subject_type', 'LIKE', '%' . $subject . '%');
            }
            if ($dateFrom = $request->input('date_from')) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo = $request->input('date_to')) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $logs = $query->paginate(30)->withQueryString();

            $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

            return Inertia::render('Admin/AuditLogs/Index', [
                'logs' => $logs,
                'filters' => $request->only(['user_id', 'action', 'subject_type', 'date_from', 'date_to']),
                'actions' => $actions,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\AuditLogController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/AuditLogs/Index', ['logs' => [], 'filters' => [], 'actions' => []]);
        }
    }

    public function recent(Request $request)
    {
        $limit = $request->integer('limit', 10);
        $logs = ActivityLog::with('user:id,name,email')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'user' => $log->user ? ['name' => $log->user->name] : null,
                'action' => $log->action,
                'subject_type' => $log->subject_type,
                'created_at' => $log->created_at->toIsoString(),
            ]);

        return response()->json($logs);
    }
}