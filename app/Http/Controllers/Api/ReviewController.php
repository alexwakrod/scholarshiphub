<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Review;
use App\Models\Application;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $validated = $request->validate([
                'scholarship_id' => 'required|exists:scholarships,id',
                'rating'         => 'required|integer|min:1|max:5',
                'comment'        => 'required|string|max:5000',
            ]);

            // Enforce that the user has an active application for this scholarship
            $application = Application::where('user_id', $user->id)
                ->where('scholarship_id', $validated['scholarship_id'])
                ->first();

            if (!$application) {
                return response()->json(['message' => 'You must apply for this scholarship before reviewing it.'], 403);
            }

            $review = Review::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'scholarship_id' => $validated['scholarship_id'],
                ],
                [
                    'rating'  => $validated['rating'],
                    'comment' => $validated['comment'],
                ]
            );

            Log::info('Review submitted via API.', [
                'review_id' => $review->id,
                'user_id'   => $user->id,
            ]);

            return response()->json(['message' => 'Review submitted.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Api\ReviewController error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Failed to submit review.'], 500);
        }
    }
}