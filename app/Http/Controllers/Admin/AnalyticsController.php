<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\AiReview;
use App\Models\MatchScore;

class AnalyticsController extends Controller
{
    /**
     * Display system analytics and metrics.
     */
    public function index()
    {
        try {
            // General counts
            $totalUsers = User::count();
            $totalStudents = User::where('role', 'student')->count();
            $totalAdmins = User::where('role', 'admin')->count();
            $activeScholarships = Scholarship::where('status', 'active')->count();
            $expiredScholarships = Scholarship::where('status', 'expired')->count();
            $totalAiReviews = AiReview::count();
            $totalApplications = \App\Models\Application::count();

            // Registration over time (last 12 months)
            $registrations = User::selectRaw("DATE_TRUNC('month', created_at) as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn($r) => ['month' => $r->month->format('Y-m'), 'count' => (int)$r->count]);

            // AI reviews over time
            $reviewsOverTime = AiReview::selectRaw("DATE_TRUNC('day', reviewed_at) as day, COUNT(*) as count")
                ->where('reviewed_at', '>=', now()->subDays(30))
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->map(fn($r) => ['day' => $r->day->format('Y-m-d'), 'count' => (int)$r->count]);

            // Match score distribution
            $scoreDistribution = MatchScore::selectRaw("
                    CASE
                        WHEN overall_score BETWEEN 0 AND 20 THEN '0-20'
                        WHEN overall_score BETWEEN 21 AND 40 THEN '21-40'
                        WHEN overall_score BETWEEN 41 AND 60 THEN '41-60'
                        WHEN overall_score BETWEEN 61 AND 80 THEN '61-80'
                        ELSE '81-100'
                    END as range, COUNT(*) as count
                ")
                ->groupBy('range')
                ->get()
                ->pluck('count', 'range');

            // Top countries
            $topCountries = Scholarship::active()
                ->selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Average match scores by scholarship category (top 5)
            $avgMatchByProvider = Scholarship::active()
                ->join('match_scores', 'scholarships.id', '=', 'match_scores.scholarship_id')
                ->selectRaw('scholarships.provider, AVG(match_scores.overall_score) as avg_score')
                ->groupBy('scholarships.provider')
                ->orderByDesc('avg_score')
                ->limit(5)
                ->get();

            return Inertia::render('Admin/Analytics/Index', [
                'totalUsers' => $totalUsers,
                'totalStudents' => $totalStudents,
                'totalAdmins' => $totalAdmins,
                'activeScholarships' => $activeScholarships,
                'expiredScholarships' => $expiredScholarships,
                'totalAiReviews' => $totalAiReviews,
                'totalApplications' => $totalApplications,
                'registrations' => $registrations,
                'reviewsOverTime' => $reviewsOverTime,
                'scoreDistribution' => $scoreDistribution,
                'topCountries' => $topCountries,
                'avgMatchByProvider' => $avgMatchByProvider,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\AnalyticsController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Analytics/Index', [])->with('error', 'Failed to load analytics.');
        }
    }
}