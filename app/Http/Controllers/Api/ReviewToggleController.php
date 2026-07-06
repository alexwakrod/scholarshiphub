<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;

class ReviewToggleController extends Controller
{
    public function toggle(Request $request, int $documentId)
    {
        $request->validate([
            'type'      => 'required|in:suggestion,grammar',
            'index'     => 'required|integer|min:0',
            'completed' => 'required|boolean',
        ]);

        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($documentId);
        $review = $document->latestReview;

        if (!$review) {
            return response()->json(['message' => 'No review found.'], 404);
        }

        $column = $request->type === 'suggestion' ? 'completed_suggestions' : 'completed_grammar_issues';
        $completions = $review->$column ?? [];

        $index = $request->index;
        // Extend array if needed
        while (count($completions) <= $index) {
            $completions[] = false;
        }
        $completions[$index] = $request->completed;
        $review->$column = $completions;
        $review->save();

        return response()->json(['message' => 'Updated.']);
    }
}