<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\AiReview;
use App\Models\MatchScore;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard (Inertia).
     */
    public function index()
    {
        $data = $this->getDashboardData();
        return Inertia::render('Admin/Dashboard', $data);
    }

    /**
     * Return the same data as JSON for real‑time polling.
     */
    public function stats(Request $request)
    {
        try {
            return response()->json($this->getDashboardData());
        } catch (\Throwable $e) {
            Log::error('Admin dashboard stats error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to load stats. Check logs.',
            ], 500);
        }
    }

    private function getDashboardData(): array
    {
        // ---------- Simple counts ----------
        $totalUsers          = User::count();
        $activeScholarships  = Scholarship::where('status', 'active')->count();
        $totalAiReviews      = AiReview::count();
        $averageMatchScore   = round(MatchScore::avg('overall_score') ?? 0, 1);

        // ---------- Registrations – last 12 months ----------
        $registrations = User::selectRaw("to_char(created_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month, // already a string like '2026-01'
                'count' => (int)$r->count,
            ]);

        // ---------- AI reviews – last 30 days ----------
        $reviewsOverTime = AiReview::selectRaw("to_char(reviewed_at, 'YYYY-MM-DD') as day, COUNT(*) as count")
            ->where('reviewed_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($r) => [
                'day'   => $r->day, // already a string like '2026-07-01'
                'count' => (int)$r->count,
            ]);

        // ---------- Match score distribution ----------
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
            ->pluck('count', 'range');

        // ---------- Top countries ----------
        $topCountries = Scholarship::active()
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'totalUsers'          => $totalUsers,
            'activeScholarships'  => $activeScholarships,
            'totalAiReviews'      => $totalAiReviews,
            'averageMatchScore'   => $averageMatchScore,
            'registrations'       => $registrations,
            'reviewsOverTime'     => $reviewsOverTime,
            'scoreDistribution'   => $scoreDistribution,
            'topCountries'        => $topCountries,
        ];
    }
}