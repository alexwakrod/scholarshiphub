<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\AiReview;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\SurveyResponse;
use App\Models\MatchScore;

class StatsController extends Controller
{
    /**
     * Return dynamic live statistics for the landing page,
     * plus the authenticated user's average match score.
     */
    public function __invoke(Request $request)
    {
        try {
            $globalData = Cache::remember('api_landing_stats', 300, function () {
                return [
                    'activeScholarships' => Scholarship::where('status', 'active')->count(),
                    'registeredStudents' => User::where('role', 'student')->count(),
                    'documentsReviewed'  => AiReview::count(),
                    'faqs'               => Faq::published()->select('id', 'question', 'answer')->get(),
                    'testimonials'       => Testimonial::approved()->latest()->take(3)->select('id', 'quote', 'name_display', 'grade')->get(),
                    'surveyStats'        => SurveyResponse::getStats(),
                ];
            });

            // Add user-specific average match score
            $avgMatchScore = 0;
            $user = Auth::user();
            if ($user && $user->studentProfile && $user->studentProfile->profile_completed) {
                $avgMatchScore = (float) MatchScore::where('student_profile_id', $user->studentProfile->id)
                    ->avg('overall_score') ?? 0;
            }

            return response()->json(array_merge($globalData, [
                'avgMatchScore' => round($avgMatchScore, 1),
            ]));
        } catch (\Throwable $e) {
            Log::error('Api\StatsController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'activeScholarships' => 0,
                'registeredStudents' => 0,
                'documentsReviewed'  => 0,
                'avgMatchScore'      => 0,
                'faqs'               => [],
                'testimonials'       => [],
                'surveyStats'        => [],
            ], 500);
        }
    }
}