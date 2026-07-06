<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Scholarship;
use App\Models\MatchScore;
use App\Models\Application;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Bookmark;
use App\Services\ProfileCompletionService;

class DashboardController extends Controller
{
    /**
     * Show the user's dashboard with comprehensive debug logging.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('DashboardController: user loaded', ['user_id' => $user->id ?? 'null']);

            $profile = $user->studentProfile;
            Log::info('DashboardController: student profile', [
                'profile_exists' => !is_null($profile),
                'profile_completed' => $profile->profile_completed ?? false,
                'profile_id' => $profile->id ?? null,
            ]);

            // Profile completion percentage
            $completionService = app(ProfileCompletionService::class);
            $profileCompletion = $profile ? $completionService->calculate($profile) : 0;
            Log::info('DashboardController: profile completion calculated', ['percent' => $profileCompletion]);

            // Top 3 matches
            $topMatches = [];
            $avgMatch = 0;

            if ($profile && $profile->profile_completed) {
                Log::info('DashboardController: profile completed, querying matches...');

                // Count match scores
                $matchCount = MatchScore::where('student_profile_id', $profile->id)->count();
                Log::info('DashboardController: match scores count', ['count' => $matchCount]);

                if ($matchCount > 0) {
                    $matchQuery = MatchScore::where('student_profile_id', $profile->id)
                        ->with('scholarship')
                        ->orderByDesc('overall_score')
                        ->take(3);

                    // Log the SQL and bindings for debugging
                    Log::info('DashboardController: match query', [
                        'sql' => $matchQuery->toSql(),
                        'bindings' => $matchQuery->getBindings(),
                    ]);

                    $topMatches = $matchQuery->get()->map(function ($score) {
                        return [
                            'id'       => $score->scholarship_id,
                            'title'    => $score->scholarship->title ?? 'Missing title',
                            'provider' => $score->scholarship->provider ?? 'Unknown',
                            'country'  => $score->scholarship->country ?? '',
                            'deadline' => $score->scholarship->deadline
                                            ? $score->scholarship->deadline->toDateString()
                                            : now()->toDateString(),
                            'score'    => $score->overall_score,
                        ];
                    });

                    $avgMatch = MatchScore::where('student_profile_id', $profile->id)->avg('overall_score') ?? 0;
                    Log::info('DashboardController: top matches fetched', [
                        'count' => $topMatches->count(),
                        'average' => $avgMatch,
                    ]);
                } else {
                    Log::warning('DashboardController: zero match scores found for completed profile.');
                }
            } else {
                Log::info('DashboardController: profile not completed or missing – skipping matches.');
            }

            // Upcoming deadlines for bookmarked scholarships within 7 days
            $upcomingDeadlines = Bookmark::where('user_id', $user->id)
                ->with('scholarship')
                ->whereHas('scholarship', function ($q) {
                    $q->where('status', 'active')
                      ->whereBetween('deadline', [now(), now()->addDays(7)]);
                })
                ->get()
                ->map(function ($bookmark) {
                    return [
                        'id'       => $bookmark->scholarship_id,
                        'title'    => $bookmark->scholarship->title,
                        'deadline' => $bookmark->scholarship->deadline->toISOString(),
                    ];
                });

            Log::info('DashboardController: upcoming deadlines', ['count' => $upcomingDeadlines->count()]);

            // Personal stats
            $activeAppsCount = Application::where('user_id', $user->id)
                ->whereIn('status', ['interested', 'applied', 'submitted'])->count();
            $documentsCount = Document::where('user_id', $user->id)->count();
            $unreadNotifications = Notification::where('user_id', $user->id)->unread()->count();

            // Average category scores for radar chart
            $categoryAverages = $this->getCategoryAverages($profile);

            Log::info('DashboardController: rendering dashboard', [
                'topMatches_count' => count($topMatches),
                'upcomingDeadlines_count' => count($upcomingDeadlines),
                'avgMatch' => $avgMatch,
            ]);

            return Inertia::render('Dashboard', [
                'user'                => $user->only('id', 'name', 'email', 'avatar_url'),
                'profileCompletion'   => $profileCompletion,
                'topMatches'          => $topMatches,
                'upcomingDeadlines'   => $upcomingDeadlines,
                'stats'               => [
                    'activeScholarships'     => Scholarship::where('status', 'active')->count(),
                    'documentsCount'         => $documentsCount,
                    'applicationsInProgress' => $activeAppsCount,
                    'avgMatchScore'          => round($avgMatch, 1),
                ],
                'unreadNotifications' => $unreadNotifications,
                'categoryAverages'    => $categoryAverages,
            ]);
        } catch (\Throwable $e) {
            Log::error('DashboardController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return Inertia::render('Dashboard', [
                'user'                => null,
                'profileCompletion'   => 0,
                'topMatches'          => [],
                'upcomingDeadlines'   => [],
                'stats'               => [],
                'unreadNotifications' => 0,
                'categoryAverages'    => [],
            ]);
        }
    }

    /**
     * Compute average match category scores across all scholarships.
     */
    private function getCategoryAverages($profile): array
    {
        try {
            if (!$profile || !$profile->profile_completed) return [];
            $scores = MatchScore::where('student_profile_id', $profile->id)->get();
            if ($scores->isEmpty()) return [];
            $categories = ['academic', 'demographic', 'interest', 'eligibility'];
            $avgs = [];
            foreach ($categories as $cat) {
                $total = 0;
                $count = 0;
                foreach ($scores as $score) {
                    $categoryScores = $score->category_scores;
                    if (isset($categoryScores[$cat])) {
                        $total += (float) $categoryScores[$cat];
                        $count++;
                    }
                }
                $avgs[$cat] = $count > 0 ? round($total / $count, 1) : 0;
            }
            return $avgs;
        } catch (\Throwable $e) {
            Log::error('DashboardController@getCategoryAverages error: ' . $e->getMessage());
            return [];
        }
    }
}