<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MatchScore;

class TopMatchesController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->studentProfile) {
            return response()->json([]);
        }

        $profile = $user->studentProfile;
        if (!$profile->profile_completed) {
            return response()->json([]);
        }

        $limit = $request->integer('limit', 5);

        $topMatches = MatchScore::where('student_profile_id', $profile->id)
            ->with('scholarship')
            ->orderByDesc('overall_score')
            ->take($limit)
            ->get()
            ->map(function ($score) {
                return [
                    'id'       => $score->scholarship_id,
                    'title'    => $score->scholarship->title,
                    'provider' => $score->scholarship->provider,
                    'country'  => $score->scholarship->country,
                    'deadline' => $score->scholarship->deadline->toDateString(),
                    'score'    => $score->overall_score,
                ];
            });

        return response()->json($topMatches);
    }
}