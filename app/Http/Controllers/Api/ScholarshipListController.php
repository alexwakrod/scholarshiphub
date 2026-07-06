<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Scholarship;
use App\Models\Bookmark;
use App\Models\MatchScore;

class ScholarshipListController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $filters = $request->validate([
                'search'          => 'nullable|string|max:255',
                'category'        => 'nullable|string',
                'country'         => 'nullable|string',
                'amount_min'      => 'nullable|numeric|min:0',
                'amount_max'      => 'nullable|numeric|min:0',
                'deadline_before' => 'nullable|date',
                'min_match'       => 'nullable|numeric|min:0|max:100',
                'sort'            => 'nullable|in:match_score,deadline,amount',
                'page'            => 'nullable|integer|min:1',
                'per_page'        => 'nullable|integer|min:1|max:50',
            ]);

            $perPage = $filters['per_page'] ?? 15;
            $query = Scholarship::query()->active();

            // --- Keyword search – tokenized, case‑insensitive, multi‑field ---
            if (!empty($filters['search'])) {
                $searchTerm = trim($filters['search']);
                // Split into words, filter out very short words (e.g., "ps")
                $words = array_filter(
                    explode(' ', $searchTerm),
                    fn($w) => mb_strlen($w) > 1
                );

                if (!empty($words)) {
                    $query->where(function ($query) use ($words) {
                        foreach ($words as $word) {
                            $word = mb_strtolower($word);
                            // Each word must be found in at least one of the fields
                            $query->where(function ($q) use ($word) {
                                $q->whereRaw('LOWER(title) LIKE ?', ['%' . $word . '%'])
                                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $word . '%'])
                                  ->orWhereRaw('LOWER(provider) LIKE ?', ['%' . $word . '%'])
                                  ->orWhereRaw('LOWER(country) LIKE ?', ['%' . $word . '%']);

                                // Include parsed_requirements (JSONB/text)
                                $driver = config('database.default');
                                $connection = config("database.connections.{$driver}.driver");
                                if ($connection === 'pgsql') {
                                    $q->orWhereRaw("LOWER(parsed_requirements::text) LIKE ?", ['%' . $word . '%']);
                                } else {
                                    // SQLite and other drivers
                                    $q->orWhereRaw('LOWER(parsed_requirements) LIKE ?', ['%' . $word . '%']);
                                }
                            });
                        }
                    });
                }
            }

            // Category filter (provider)
            if (!empty($filters['category'])) {
                $query->where('provider', 'ILIKE', '%' . $filters['category'] . '%');
            }

            // Country filter
            if (!empty($filters['country'])) {
                $query->where('country', 'ILIKE', '%' . $filters['country'] . '%');
            }

            // Amount range
            if (isset($filters['amount_min'])) {
                $query->where('amount', '>=', $filters['amount_min']);
            }
            if (isset($filters['amount_max'])) {
                $query->where('amount', '<=', $filters['amount_max']);
            }

            // Deadline before
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
                default:
                    $query->orderBy('deadline', 'asc');
            }

            $paginator = $query->paginate($perPage);

            // Load match scores and bookmarks for the authenticated user
            $user = Auth::user();
            $bookmarkedIds = [];
            $matchScores = [];
            if ($user && $user->studentProfile) {
                $profileId = $user->studentProfile->id;
                $bookmarkedIds = Bookmark::where('user_id', $user->id)
                    ->pluck('scholarship_id')
                    ->toArray();
                $matchScores = MatchScore::where('student_profile_id', $profileId)
                    ->whereIn('scholarship_id', $paginator->pluck('id'))
                    ->pluck('overall_score', 'scholarship_id')
                    ->toArray();
            }

            $result = $paginator->through(function ($scholarship) use ($matchScores) {
                return [
                    'id'          => $scholarship->id,
                    'title'       => $scholarship->title,
                    'provider'    => $scholarship->provider,
                    'country'     => $scholarship->country,
                    'amount'      => $scholarship->amount,
                    'deadline'    => $scholarship->deadline->toDateString(),
                    'status'      => $scholarship->status,
                    'match_score' => $matchScores[$scholarship->id] ?? null,
                ];
            });

            return response()->json([
                'data'          => $result->items(),
                'current_page'  => $result->currentPage(),
                'next_page_url' => $result->nextPageUrl(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Api\ScholarshipListController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to load scholarships.'], 500);
        }
    }
}