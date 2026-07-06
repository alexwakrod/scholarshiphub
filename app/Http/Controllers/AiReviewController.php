<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use App\Models\Document;
use App\Jobs\AiReviewJob;

class AiReviewController extends Controller
{
    /**
     * Request an AI review for a document.
     */
    public function requestReview(int $documentId)
    {
        try {
            $document = Document::where('user_id', Auth::id())->findOrFail($documentId);

            // Rate limiting per user per day
            $key = 'ai-review:' . Auth::id();
            $maxAttempts = config('scholarship.rate_limits.document_reviews_per_day', 10);
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                return back()->with('error', "Rate limit exceeded. Try again in {$seconds} seconds.");
            }
            RateLimiter::hit($key, 86400);

            // Dispatch review job
            AiReviewJob::dispatch($document);

            Log::info('AI review requested.', ['document_id' => $document->id, 'user_id' => Auth::id()]);

            return back()->with('success', 'Review is being processed. You will be notified when complete.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Document not found for review request: ' . $documentId);
            return redirect()->route('documents.index')->with('error', 'Document not found.');
        } catch (\Throwable $e) {
            Log::error('AiReviewController@requestReview error: ' . $e->getMessage(), ['document_id' => $documentId, 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to request review.');
        }
    }

    /**
     * Show the latest AI review status and results for a document.
     */
    public function show(int $documentId)
    {
        try {
            $document = Document::where('user_id', Auth::id())->findOrFail($documentId);
            $review = $document->latestReview;

            return Inertia::render('Documents/Review', [
                'document' => $document,
                'review' => $review ? [
                    'quality_score' => $review->quality_score,
                    'ats_score' => $review->ats_score,
                    'competitiveness_score' => $review->competitiveness_score,
                    'strengths' => $review->strengths,
                    'weaknesses' => $review->weaknesses,
                    'suggestions' => $review->suggestions,
                    'grammar_issues' => $review->grammar_issues,
                    'reviewed_at' => $review->reviewed_at->toISOString(),
                ] : null,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Document not found for review view: ' . $documentId);
            return redirect()->route('documents.index')->with('error', 'Document not found.');
        } catch (\Throwable $e) {
            Log::error('AiReviewController@show error: ' . $e->getMessage(), ['document_id' => $documentId, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('documents.index')->with('error', 'An error occurred.');
        }
    }
}