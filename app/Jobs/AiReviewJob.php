<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Models\AiReview;
use App\Models\Notification;
use App\Services\QwenCloudService;
use App\Traits\TracksProgress;

class AiReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksProgress;

    public $timeout = 600;
    public $tries = 2;

    private Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->onQueue('ai');
    }

    public function handle(QwenCloudService $qwen): void
    {
        $this->startTracking(
            $this->document->user_id,
            'AiReview',
            'AI review for ' . $this->document->file_name
        );

        if ($this->jobStatusRecord) {
            $this->jobStatusRecord->meta = ['document_id' => $this->document->id];
            $this->jobStatusRecord->save();
        }

        try {
            $text = $this->document->text_extracted;
            if (empty(trim($text))) {
                Log::warning('AiReviewJob: no text extracted for document ' . $this->document->id);
                $this->markCompleted();
                return;
            }

            $this->updateProgress(10, 'processing');

            $maxChars = config('scholarship.ai.max_tokens', 3000) * 4;
            if (mb_strlen($text) > $maxChars) {
                $text = mb_substr($text, 0, $maxChars);
            }

            $prompt = $this->buildPrompt($text);
            $this->updateProgress(20, 'processing');

            $response = $qwen->sendChatRequestRotating('', $prompt, 3000, 0.1);
            if (empty(trim($response))) {
                Log::warning('AiReviewJob: Qwen Cloud returned empty response for document ' . $this->document->id);
                $this->markFailed('No response from AI service.');
                return;
            }

            Log::debug('AiReviewJob raw response (first 500 chars): ' . substr($response, 0, 500));

            $this->updateProgress(70, 'processing');

            $reviewData = $this->parseResponse($response);

            $suggestionCount = count($reviewData['suggestions'] ?? []);
            $grammarCount    = count($reviewData['grammar_issues'] ?? []);

            AiReview::create([
                'document_id'              => $this->document->id,
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

            $this->updateProgress(90, 'processing');
            $this->notifyUser();
            $this->markCompleted();

            Log::info('AiReviewJob completed.', ['document_id' => $this->document->id]);
        } catch (\Throwable $e) {
            Log::error('AiReviewJob failed: ' . $e->getMessage(), [
                'document_id' => $this->document->id,
                'trace'       => $e->getTraceAsString(),
            ]);
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }

    private function buildPrompt(string $text): string
    {
        return <<<EOT
You are a strict, professional admissions document reviewer. Your task is to analyze the provided document and return a JSON object with the keys below. Follow every rule meticulously.

RULES:
1. Only include grammar issues that appear in the document. Do NOT invent issues.
2. Each grammar issue MUST include a "comment" explaining the mistake in a clear, educational way (e.g., "Missing comma after introductory phrase", "Subject-verb agreement error: singular subject requires singular verb").
3. Do NOT include the example mentioned in this prompt. Only real mistakes from the document.
4. Return ONLY valid JSON. No extra text, no markdown fences, no explanations outside the JSON.
5. The "correction" must be the exact corrected phrase as it should appear.
6. For scores, be honest and critical. Do not inflate or deflate without reason.
7. Provide specific, actionable suggestions and weaknesses, not generic statements.
8. Ensure the JSON is well-formed and all keys are present.

JSON KEYS:
- quality_score: integer 1-10
- ats_score: integer 1-10
- competitiveness_score: integer 1-10
- strengths: array of strings (3-5 specific bullet points)
- weaknesses: array of strings (3-5 specific bullet points)
- suggestions: array of strings (3-5 actionable improvement suggestions)
- grammar_issues: array of objects. Each object must have:
   "text": the exact incorrect phrase from the document
   "correction": the corrected version
   "comment": a short, educational description of the grammar mistake

Document:
{$text}
EOT;
    }

    private function parseResponse(string $response): array
    {
        $response = preg_replace('/^```(?:json)?\s*\n?/m', '', $response);
        $response = preg_replace('/\n?```$/m', '', $response);
        $response = trim($response);

        $jsonStart = strpos($response, '{');
        $arrayStart = strpos($response, '[');
        if ($jsonStart === false && $arrayStart === false) {
            throw new \Exception('No JSON structure found in AI response: ' . substr($response, 0, 200));
        }

        $start = ($jsonStart === false) ? $arrayStart : ($arrayStart === false ? $jsonStart : min($jsonStart, $arrayStart));
        $end   = strrpos($response, $jsonStart !== false ? '}' : ']');

        if ($end === false) {
            throw new \Exception('Unterminated JSON in AI response: ' . substr($response, 0, 200));
        }

        $jsonString = substr($response, $start, $end - $start + 1);
        $parsed = json_decode($jsonString, true);

        if (is_array($parsed)) {
            return $this->sanitize($parsed);
        }

        Log::warning('AiReviewJob: could not decode JSON, attempting regex extraction.');
        $extracted = [
            'quality_score'         => $this->extractFloat($response, 'quality_score'),
            'ats_score'             => $this->extractFloat($response, 'ats_score'),
            'competitiveness_score' => $this->extractFloat($response, 'competitiveness_score'),
            'strengths'             => $this->extractArray($response, 'strengths'),
            'weaknesses'            => $this->extractArray($response, 'weaknesses'),
            'suggestions'           => $this->extractArray($response, 'suggestions'),
            'grammar_issues'        => $this->extractGrammar($response),
        ];

        if ($extracted['quality_score'] !== null) {
            return $this->sanitize($extracted);
        }

        throw new \Exception('Unable to extract valid JSON or key fields from AI response: ' . substr($response, 0, 300));
    }

    private function extractFloat(string $response, string $key): ?float
    {
        if (preg_match('/"' . $key . '"\s*:\s*([0-9]+(?:\.[0-9]+)?)/i', $response, $m)) {
            return (float) $m[1];
        }
        return null;
    }

    private function extractArray(string $response, string $key): array
    {
        if (preg_match('/"' . $key . '"\s*:\s*\[(.*?)\]/s', $response, $m)) {
            $content = $m[1];
            $items = [];
            if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/', $content, $matches)) {
                $items = $matches[1];
                $items = array_map(fn($s) => str_replace(['\"', '\\\\'], ['"', '\\'], $s), $items);
            }
            return $items;
        }
        return [];
    }

    private function extractGrammar(string $response): array
    {
        $issues = [];
        // Standard JSON object format with text, correction, comment
        if (preg_match_all('/\{[^}]*"text"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"[^}]*"correction"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"(?:[^}]*"comment"\s*:\s*"((?:[^"\\\\]|\\\\.)*)")?[^}]*\}/s', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $issues[] = [
                    'text'       => str_replace(['\"', '\\\\'], ['"', '\\'], $m[1]),
                    'correction' => str_replace(['\"', '\\\\'], ['"', '\\'], $m[2]),
                    'comment'    => isset($m[3]) ? str_replace(['\"', '\\\\'], ['"', '\\'], $m[3]) : '',
                ];
            }
        }
        // Fallback arrow format
        if (empty($issues) && preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"\s*(?:→|->)\s*"((?:[^"\\\\]|\\\\.)*)"/u', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $issues[] = [
                    'text'       => $m[1],
                    'correction' => $m[2],
                    'comment'    => '',
                ];
            }
        }
        return $issues;
    }

    private function sanitize(array $data): array
    {
        $grammarIssues = $data['grammar_issues'] ?? [];

        // Remove any issues that match the example from the prompt
        $grammarIssues = array_filter($grammarIssues, function ($issue) {
            $text = is_array($issue) ? ($issue['text'] ?? '') : '';
            return !in_array(trim($text), ['He go to school', 'He goes to school', '']);
        });

        // Deduplicate by text
        $seen = [];
        $unique = [];
        foreach ($grammarIssues as $issue) {
            $key = strtolower(trim(is_array($issue) ? ($issue['text'] ?? '') : (string) $issue));
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $unique[] = [
                    'text'       => is_array($issue) ? ($issue['text'] ?? '') : (string) $issue,
                    'correction' => is_array($issue) ? ($issue['correction'] ?? '') : '',
                    'comment'    => is_array($issue) ? ($issue['comment'] ?? '') : '',
                ];
            }
        }

        return [
            'quality_score'         => isset($data['quality_score']) ? max(1, min(10, (float) $data['quality_score'])) : 5.0,
            'ats_score'             => isset($data['ats_score']) ? max(1, min(10, (float) $data['ats_score'])) : 5.0,
            'competitiveness_score' => isset($data['competitiveness_score']) ? max(1, min(10, (float) $data['competitiveness_score'])) : 5.0,
            'strengths'             => is_array($data['strengths'] ?? null) ? array_values(array_unique($data['strengths'])) : [],
            'weaknesses'            => is_array($data['weaknesses'] ?? null) ? array_values(array_unique($data['weaknesses'])) : [],
            'suggestions'           => is_array($data['suggestions'] ?? null) ? array_values(array_unique($data['suggestions'])) : [],
            'grammar_issues'        => $unique,
        ];
    }

    private function notifyUser(): void
    {
        try {
            $user = $this->document->user;
            if (!$user) return;
            Notification::create([
                'user_id' => $user->id,
                'type'    => 'review_completed',
                'data'    => [
                    'message'     => 'Your ' . $this->document->file_type . ' document review is complete.',
                    'document_id' => $this->document->id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('AiReviewJob notifyUser error: ' . $e->getMessage());
        }
    }
}