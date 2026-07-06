<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use App\Models\JobStatus;

class DocumentReviewStatusController extends Controller
{
    public function show(int $documentId)
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($documentId);

        // 1. If a review exists, it's definitely completed
        if ($document->latestReview) {
            return response()->json([
                'status'   => 'completed',
                'progress' => 100,
            ]);
        }

        // 2. Otherwise, check for an active job to report progress
        $activeJob = JobStatus::where('type', 'AiReview')
            ->where('user_id', $user->id)
            ->where('status', 'processing')
            ->latest('created_at')
            ->first();

        // If no active job but also no review, assume idle
        if (!$activeJob) {
            return response()->json([
                'status'   => 'idle',
                'progress' => 0,
            ]);
        }

        // Job is still running – return its progress
        return response()->json([
            'status'   => 'processing',
            'progress' => $activeJob->progress ?? 5,
        ]);
    }
}