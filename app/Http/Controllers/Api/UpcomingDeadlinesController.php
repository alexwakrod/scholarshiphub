<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bookmark;
use App\Models\Scholarship;

class UpcomingDeadlinesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        $deadlines = Bookmark::where('user_id', $user->id)
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

        return response()->json($deadlines);
    }
}