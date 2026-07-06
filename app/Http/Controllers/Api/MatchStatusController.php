<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\StudentProfile;
use App\Models\MatchScore;

class MatchStatusController extends Controller
{
    /**
     * Return recomputation progress for match scores.
     */
    public function __invoke(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $profile = $user->studentProfile;
            if (!$profile) {
                return response()->json([
                    'status' => 'no_profile',
                    'progress' => 0,
                    'total_scholarships' => 0,
                    'computed' => 0,
                ]);
            }

            $total = \App\Models\Scholarship::active()->count();
            $computed = MatchScore::where('student_profile_id', $profile->id)->count();
            $progress = $total > 0 ? round(($computed / $total) * 100, 2) : 0;
            $status = ($progress >= 100) ? 'completed' : 'in_progress';

            return response()->json([
                'status' => $status,
                'progress' => $progress,
                'total_scholarships' => $total,
                'computed' => $computed,
            ]);
        } catch (\Throwable $e) {
            Log::error('Api\MatchStatusController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal error.'], 500);
        }
    }
}