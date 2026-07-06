<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Scholarship;
use App\Models\Bookmark;
use App\Models\MatchScore;

class ScholarshipController extends Controller
{
    /**
     * List scholarships with filtering, sorting, and pagination.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->validate([
                'search' => 'nullable|string|max:255',
                'category' => 'nullable|string',
                'country' => 'nullable|string',
                'amount_min' => 'nullable|numeric|min:0',
                'amount_max' => 'nullable|numeric|min:0',
                'deadline_before' => 'nullable|date',
                'min_match' => 'nullable|numeric|min:0|max:100',
                'sort' => 'nullable|in:match_score,deadline,amount',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $perPage = $filters['per_page'] ?? 15;
            $page = $filters['page'] ?? 1;

            $cacheKey = 'scholarships_list_' . md5(serialize($filters) . '_page' . $page);
            $data = Cache::remember($cacheKey, config('scholarship.cache_ttls.scholarships_list', 3600), function () use ($filters, $perPage) {
                $query = Scholarship::query()->active();

                if (!empty($filters['search'])) {
                    $query->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$filters['search']]);
                }

                if (!empty($filters['category'])) {
                    // Assume parsed_requirements->>'academic_level' or something; for now use basic filter on provider
                    $query->where('provider', 'ILIKE', '%' . $filters['category'] . '%');
                }

                if (!empty($filters['country'])) {
                    $query->where('country', 'ILIKE', '%' . $filters['country'] . '%');
                }

                if (isset($filters['amount_min'])) {
                    $query->where('amount', '>=', $filters['amount_min']);
                }
                if (isset($filters['amount_max'])) {
                    $query->where('amount', '<=', $filters['amount_max']);
                }
                if (!empty($filters['deadline_before'])) {
                    $query->where('deadline', '<=', $filters['deadline_before']);
                }

                // Sorting
                $sort = $filters['sort'] ?? 'deadline';
                switch ($sort) {
                    case 'amount':
                        $query->orderBy('amount', 'desc');
                        break;
                    case 'deadline':
                        $query->orderBy('deadline', 'asc');
                        break;
                    default:
                        $query->orderBy('deadline', 'asc');
                }

                return $query->paginate($perPage);
            });

            // Get user bookmarks and match scores if authenticated
            $user = Auth::user();
            $bookmarkedIds = [];
            $matchScores = [];
            if ($user && $user->studentProfile) {
                $profileId = $user->studentProfile->id;
                $bookmarkedIds = Bookmark::where('user_id', $user->id)->pluck('scholarship_id')->toArray();
                $matchScores = MatchScore::where('student_profile_id', $profileId)
                    ->whereIn('scholarship_id', $data->pluck('id'))
                    ->pluck('overall_score', 'scholarship_id')
                    ->toArray();
            }

            // Available filter options (distinct countries, categories)
            $countries = Cache::remember('scholarships_countries', 3600, fn () =>
                Scholarship::select('country')->distinct()->orderBy('country')->pluck('country')
            );
            $categories = Cache::remember('scholarships_categories', 3600, fn () =>
                Scholarship::select('provider as name')->distinct()->orderBy('provider')->pluck('provider')
            );

            return Inertia::render('Scholarships/Index', [
                'scholarships' => $data,
                'filters' => $filters,
                'countries' => $countries,
                'categories' => $categories,
                'bookmarkedIds' => $bookmarkedIds,
                'matchScores' => $matchScores,
            ]);
        } catch (\Throwable $e) {
            Log::error('ScholarshipController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Scholarships/Index', [
                'scholarships' => [],
                'filters' => [],
                'countries' => [],
                'categories' => [],
                'bookmarkedIds' => [],
                'matchScores' => [],
            ])->with('error', 'Failed to load scholarships.');
        }
    }

    /**
     * Show a specific scholarship with detailed data.
     */
    public function show(int $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            $user = Auth::user();
            $matchScore = null;
            $application = null;
            $canReview = false;
            $reviews = [];

            if ($user) {
                $profile = $user->studentProfile;
                if ($profile && $profile->profile_completed) {
                    $matchScore = MatchScore::where('student_profile_id', $profile->id)
                        ->where('scholarship_id', $id)
                        ->first();
                }
                $application = \App\Models\Application::where('user_id', $user->id)
                    ->where('scholarship_id', $id)
                    ->first();
                if ($application) $canReview = true;
                $reviews = \App\Models\Review::with('user')
                    ->where('scholarship_id', $id)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'user_name' => $r->user->name ?? 'Anonymous',
                        'rating' => $r->rating,
                        'comment' => $r->comment,
                        'created_at' => $r->created_at->toDateString(),
                    ]);
            }

            return Inertia::render('Scholarships/Show', [
                'scholarship' => $scholarship,
                'matchScore' => $matchScore,
                'application' => $application,
                'canReview' => $canReview,
                'reviews' => $reviews,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Scholarship not found: ' . $id);
            return redirect()->route('scholarships.index')->with('error', 'Scholarship not found.');
        } catch (\Throwable $e) {
            Log::error('ScholarshipController@show error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('scholarships.index')->with('error', 'An error occurred.');
        }
    }
}