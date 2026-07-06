<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use App\Models\AiReview;
use App\Models\Notification;
use App\Models\JobStatus;
use App\Services\QwenCloudService;
use App\Services\AiReviewParser;
use Illuminate\Support\Str;

class DocumentReviewStreamController extends Controller
{
    public function __invoke(Request $request, int $documentId)
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($documentId);

        // Create a job status record for tracking
        $jobStatus = JobStatus::create([
            'job_uuid'   => (string) Str::uuid(),
            'user_id'    => $user->id,
            'type'       => 'AiReview',
            'label'      => 'AI review for ' . $document->file_name,
            'status'     => 'processing',
            'progress'   => 0,
            'started_at' => now(),
            'meta'       => ['document_id' => $document->id, 'partial_text' => ''],
        ]);

        $text = $document->text_extracted;
        if (empty(trim($text))) {
            return response()->stream(function () {
                $this->sendSSE('error', ['message' => 'No text extracted']);
            }, 200, $this->sseHeaders());
        }

        $maxChars = config('scholarship.ai.max_tokens', 3000) * 4;
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars);
        }

        $prompt = $this->buildPrompt($text);

        return response()->stream(function () use ($prompt, $document, $jobStatus) {
            if (ob_get_level()) ob_end_flush();

            $this->sendSSE('status', ['status' => 'started', 'progress' => 5]);

            $qwen = app(QwenCloudService::class);
            $fullResponse = '';

            try {
                $fullResponse = $qwen->streamChatCompletion(
                    '',
                    $prompt,
                    2000,
                    0.2,
                    function (string $chunk) use ($jobStatus) {
                        // Send chunk to frontend
                        $this->sendSSE('chunk', ['text' => $chunk]);
                        // Update job status meta
                        $meta = $jobStatus->meta ?? [];
                        $meta['partial_text'] = ($meta['partial_text'] ?? '') . $chunk;
                        $jobStatus->meta = $meta;
                        $jobStatus->save();
                    }
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SSE stream error: ' . $e->getMessage());
                $this->sendSSE('error', ['message' => $e->getMessage()]);
                $jobStatus->update(['status' => 'failed', 'finished_at' => now()]);
                return;
            }

            if (empty(trim($fullResponse))) {
                $this->sendSSE('error', ['message' => 'No response from AI']);
                $jobStatus->update(['status' => 'failed', 'finished_at' => now()]);
                return;
            }

            // Parse the full response
            try {
                $reviewData = AiReviewParser::parse($fullResponse);
            } catch (\Throwable $e) {
                $this->sendSSE('error', ['message' => 'Parsing failed: ' . $e->getMessage()]);
                $jobStatus->update(['status' => 'failed', 'finished_at' => now()]);
                return;
            }

            $suggestionCount = count($reviewData['suggestions'] ?? []);
            $grammarCount    = count($reviewData['grammar_issues'] ?? []);

            AiReview::create([
                'document_id'              => $document->id,
                'quality_score'            => $reviewData['quality_score'],
                'ats_score'                => $reviewData['ats_score'],
                'competitiveness_score'    => $reviewData['competitiveness_score'],
                'strengths'                => $reviewData['strengths'],
                'weaknesses'               => $reviewData['weaknesses'],
                'suggestions'              => $reviewData['suggestions'],
                'grammar_issues'           => $reviewData['grammar_issues'],
                'reviewed_at'              => now(),
                'completed_suggestions'    => array_fill(0, $suggestionCount, false),
                'completed_grammar_issues' => array_fill(0, $grammarCount, false),
            ]);

            Notification::create([
                'user_id' => $document->user_id,
                'type'    => 'review_completed',
                'data'    => [
                    'message'     => 'Your ' . $document->file_type . ' document review is complete.',
                    'document_id' => $document->id,
                ],
            ]);

            $jobStatus->update(['status' => 'completed', 'progress' => 100, 'finished_at' => now()]);

            $this->sendSSE('completed', ['review_id' => $document->id]);
        }, 200, $this->sseHeaders());
    }

    private function buildPrompt(string $text): string
    {
        return <<<EOT
You are an expert admissions document reviewer. Analyze the following document and provide a structured JSON evaluation.

Return ONLY valid JSON with these keys:
- quality_score: number (1-10)
- ats_score: number (1-10)
- competitiveness_score: number (1-10)
- strengths: array of strings (3-5 bullet points)
- weaknesses: array of strings (3-5 bullet points)
- suggestions: array of strings (3-5 actionable improvement suggestions)
- grammar_issues: array of objects, each with exactly two keys:
   "text": the incorrect original phrase from the document,
   "correction": the corrected version.
   Example: {"text":"He go to school","correction":"He goes to school"}
   If there are no grammar issues, return an empty array [].

Document:
{$text}
EOT;
    }

    private function sendSSE(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        flush();
    }

    private function sseHeaders(): array
    {
        return [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];
    }
}