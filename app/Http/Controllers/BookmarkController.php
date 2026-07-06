<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Bookmark;
use App\Models\Scholarship;

class BookmarkController extends Controller
{
    /**
     * List all bookmarked scholarships for the user.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return Inertia::render('Bookmarks/Index', ['bookmarks' => []]);
            }

            // Use a direct join to avoid broken eager‑load on composite‑key table
            $bookmarks = \DB::table('bookmarks')
                ->join('scholarships', 'bookmarks.scholarship_id', '=', 'scholarships.id')
                ->where('bookmarks.user_id', $user->id)
                ->select(
                    'scholarships.id',
                    'scholarships.title',
                    'scholarships.provider',
                    'scholarships.country',
                    'scholarships.deadline',
                    'scholarships.amount',
                    'scholarships.status',
                    'bookmarks.created_at as bookmarked_at'
                )
                ->get()
                ->map(function ($row) {
                    return [
                        'id'            => $row->id,
                        'title'         => $row->title,
                        'provider'      => $row->provider,
                        'country'       => $row->country,
                        'deadline'      => $row->deadline ? \Carbon\Carbon::parse($row->deadline)->toDateString() : '',
                        'amount'        => $row->amount,
                        'status'        => $row->status,
                        'bookmarked_at' => $row->bookmarked_at ? \Carbon\Carbon::parse($row->bookmarked_at)->toDateString() : '',
                    ];
                });

            Log::info('Bookmarks loaded', ['user_id' => $user->id, 'count' => $bookmarks->count()]);

            return Inertia::render('Bookmarks/Index', [
                'bookmarks' => $bookmarks,
            ]);
        } catch (\Throwable $e) {
            Log::error('BookmarkController@index error: ' . $e->getMessage());
            return Inertia::render('Bookmarks/Index', ['bookmarks' => []]);
        }
    }

    /**
     * Toggle bookmark status for a scholarship.
     */
    public function toggle(int $scholarshipId)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            Scholarship::findOrFail($scholarshipId); // validate existence

            $result = Bookmark::toggle($userId, $scholarshipId);

            Log::info("Bookmark toggled", [
                'user_id' => $userId,
                'scholarship_id' => $scholarshipId,
                'status' => $result ? 'bookmarked' : 'unbookmarked',
            ]);

            return response()->json([
                'status' => $result ? 'bookmarked' : 'unbookmarked',
                'message' => $result ? 'Bookmark added.' : 'Bookmark removed.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('Bookmark toggle error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}