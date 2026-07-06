<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MatchScore;

class MatchBreakdownController extends Controller
{
    /**
     * Return detailed category scores for a scholarship, used in RadarChart.
     */
    public function __invoke(Request $request, int $scholarshipId)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->studentProfile) {
                return response()->json(['message' => 'Unauthenticated or profile incomplete.'], 401);
            }

            $profileId = $user->studentProfile->id;
            $match = MatchScore::where('student_profile_id', $profileId)
                ->where('scholarship_id', $scholarshipId)
                ->first();

            if (!$match) {
                return response()->json(['message' => 'No match score found.'], 404);
            }

            return response()->json([
                'overall_score' => $match->overall_score,
                'category_scores' => $match->category_scores,
                'computed_at' => $match->computed_at->toISOString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Api\MatchBreakdownController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal error.'], 500);
        }
    }
}