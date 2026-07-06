<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\JobStatus;

class ActiveJobsController extends Controller
{
    /**
     * Return all active (pending/processing) jobs for the authenticated user.
     */
    public function __invoke(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([]);
            }

            $jobs = JobStatus::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function (JobStatus $job) {
                    return [
                        'id'       => $job->id,
                        'label'    => $job->label ?? 'Background job',
                        'status'   => $job->status,
                        'progress' => $job->progress,
                    ];
                });

            return response()->json($jobs);
        } catch (\Throwable $e) {
            Log::error('ActiveJobsController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([]);
        }
    }
}