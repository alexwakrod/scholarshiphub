<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\SharedDashboard;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\AiReview;
use App\Models\MatchScore;
use Illuminate\Support\Facades\DB;

class SharedDashboardController extends Controller
{
    public function show(string $token)
    {
        try {
            $share = SharedDashboard::where('token', $token)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->firstOrFail();

            $widgetData = $this->aggregateWidgetData($share->widget_ids);

            return Inertia::render('Public/SharedDashboard', [
                'dashboardName' => $share->name,
                'widgets'       => $share->widget_ids,
                'widgetData'    => $widgetData,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response('Dashboard not found or expired.', 404);
        } catch (\Throwable $e) {
            Log::error('Public shared dashboard error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response('An error occurred.', 500);
        }
    }

    private function aggregateWidgetData(array $widgetIds): array
    {
        $data = [];

        if (in_array('stats', $widgetIds)) {
            $data['stats'] = [
                'activeScholarships' => Scholarship::where('status', 'active')->count(),
                'totalStudents'      => User::where('role', 'student')->count(),
                'totalAiReviews'     => AiReview::count(),
            ];
        }

        if (in_array('registrations_chart', $widgetIds)) {
            $registrations = User::selectRaw("DATE_TRUNC('month', created_at) as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $data['registrations'] = [
                'labels' => $registrations->pluck('month')->map(fn($d) => $d->format('Y-m'))->toArray(),
                'values' => $registrations->pluck('count')->toArray(),
            ];
        }

        if (in_array('reviews_chart', $widgetIds)) {
            $reviews = AiReview::selectRaw("DATE_TRUNC('day', reviewed_at) as day, COUNT(*) as count")
                ->where('reviewed_at', '>=', now()->subDays(30))
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $data['reviews'] = [
                'labels' => $reviews->pluck('day')->map(fn($d) => $d->format('Y-m-d'))->toArray(),
                'values' => $reviews->pluck('count')->toArray(),
            ];
        }

        if (in_array('score_distribution', $widgetIds)) {
            $distribution = MatchScore::selectRaw("
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

            $data['scoreDistribution'] = [
                'labels' => $distribution->keys()->toArray(),
                'values' => $distribution->values()->toArray(),
            ];
        }

        if (in_array('top_countries', $widgetIds)) {
            $countries = Scholarship::active()
                ->selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            $data['topCountries'] = [
                'labels' => $countries->pluck('country')->toArray(),
                'values' => $countries->pluck('count')->toArray(),
            ];
        }

        if (in_array('avg_match_provider', $widgetIds)) {
            $avgMatch = Scholarship::active()
                ->join('match_scores', 'scholarships.id', '=', 'match_scores.scholarship_id')
                ->selectRaw('scholarships.provider, AVG(match_scores.overall_score) as avg_score')
                ->groupBy('scholarships.provider')
                ->orderByDesc('avg_score')
                ->limit(5)
                ->get();

            $data['avgMatchByProvider'] = [
                'labels' => $avgMatch->pluck('provider')->toArray(),
                'values' => $avgMatch->pluck('avg_score')->map(fn($v) => round($v, 1))->toArray(),
            ];
        }

        return $data;
    }
}