<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Pathway;
use App\Models\MatchScore;
use App\Models\Notification;
use App\Services\QwenCloudService;
use App\Traits\TracksProgress;

class GeneratePathwayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksProgress;

    public $timeout = 600;
    public $tries = 2;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->onQueue('ai');
    }

    public function handle(QwenCloudService $qwen): void
    {
        $this->startTracking(
            $this->user->id,
            'PathwayGeneration',
            'Strategic pathway for ' . $this->user->name
        );

        try {
            $profile = $this->user->studentProfile;
            if (!$profile || !$profile->profile_completed) {
                Log::warning('GeneratePathwayJob: profile not complete for user ' . $this->user->id);
                $this->markCompleted();
                return;
            }

            $this->updateProgress(10, 'processing');

            $topMatches = MatchScore::where('student_profile_id', $profile->id)
                ->with('scholarship')
                ->orderByDesc('overall_score')
                ->take(5)
                ->get()
                ->map(fn($ms) => [
                    'title'    => $ms->scholarship->title ?? 'Unknown',
                    'provider' => $ms->scholarship->provider ?? '',
                    'country'  => $ms->scholarship->country ?? '',
                    'deadline' => $ms->scholarship->deadline?->toDateString() ?? '',
                    'score'    => $ms->overall_score,
                ])
                ->toArray();

            if (empty($topMatches)) {
                Log::warning('GeneratePathwayJob: no top matches found for user ' . $this->user->id);
                $this->markFailed('No scholarship matches found. Please update your profile.');
                return;
            }

            $profileData = [
                'academic_level'      => $profile->academic_level,
                'major'               => $profile->major,
                'gpa'                 => $profile->gpa,
                'country'             => $profile->country,
                'english_proficiency' => $profile->english_proficiency,
                'interests'           => $profile->interests ?? [],
                'languages'           => $profile->languages ?? [],
            ];

            $prompt = $this->buildPrompt($profileData, $topMatches);
            $this->updateProgress(20, 'processing');

            $response = $qwen->sendChatRequestRotating('', $prompt, 2500, 0.3);

            if (empty(trim($response))) {
                Log::warning('GeneratePathwayJob: Qwen Cloud returned empty response for user ' . $this->user->id);
                $this->markFailed('No response from AI service.');
                return;
            }

            Log::debug('GeneratePathwayJob raw response (first 500 chars): ' . substr($response, 0, 500));

            $this->updateProgress(70, 'processing');

            $parsed = $this->parseResponse($response);

            $pathway = Pathway::create([
                'user_id'            => $this->user->id,
                'generated_at'       => now(),
                'ai_explanation'     => $parsed['overall_strategy'],
                'milestone_sequence' => $parsed['steps'],
            ]);

            $this->updateProgress(90, 'processing');

            // Build absolute URL safely for any context (queue friendly)
            $pathwayUrl = url('/pathway');

            // Create notification – isolated try/catch so a DB hiccup doesn't crash the job
            try {
                Notification::create([
                    'user_id' => $this->user->id,
                    'type'    => 'pathway_generated',
                    'data'    => [
                        'message'    => 'Your strategic pathway has been generated.',
                        'pathway_id' => $pathway->id,
                        'url'        => $pathwayUrl,
                    ],
                ]);
                Log::info('GeneratePathwayJob: notification created for user ' . $this->user->id);
            } catch (\Throwable $e) {
                Log::error('GeneratePathwayJob: failed to create notification', [
                    'user_id' => $this->user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            $this->markCompleted();

            Log::info('GeneratePathwayJob completed.', [
                'user_id'    => $this->user->id,
                'pathway_id' => $pathway->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('GeneratePathwayJob failed: ' . $e->getMessage(), [
                'user_id' => $this->user->id,
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }

    private function buildPrompt(array $profile, array $topMatches): string
    {
        $profileJson = json_encode($profile, JSON_PRETTY_PRINT);
        $matchesJson = json_encode($topMatches, JSON_PRETTY_PRINT);
        $today = now()->toDateString();

        return <<<EOT
You are an expert scholarship strategist. Based on the following student profile and their top scholarship matches, generate a personalized strategic pathway.

Today's date is **{$today}**. All suggested milestone dates MUST be in the future – never in the past. Use realistic but forward-looking deadlines, considering the scholarship deadlines listed below. If a deadline is soon, suggest earlier steps; if later, space steps accordingly.

RULES:
1. Provide a clear, detailed overall strategy that guides the student.
2. Create 5-8 specific, actionable milestone steps with realistic dates.
3. Each step should have a label, a suggested completion date (YYYY-MM-DD), and an estimated score improvement (1-100).
4. Order the steps chronologically.
5. All dates must be > {$today}. Use the scholarship deadlines as reference points but ensure steps precede them logically.
6. Return ONLY valid JSON. No extra text, no markdown fences.

JSON KEYS:
- overall_strategy: string (detailed explanation of the plan)
- steps: array of objects, each with:
   - label: string (short description of the step)
   - date: string (YYYY-MM-DD suggested completion date)
   - score: number (1-100 estimated score improvement if completed)

Student profile:
{$profileJson}

Top scholarship matches:
{$matchesJson}
EOT;
    }

    private function parseResponse(string $response): array
    {
        $response = preg_replace('/^```(?:json)?\s*\n?/m', '', $response);
        $response = preg_replace('/\n?```$/m', '', $response);
        $response = trim($response);

        $jsonStart = strpos($response, '{');
        if ($jsonStart === false) {
            throw new \Exception('No JSON structure found in AI response: ' . substr($response, 0, 200));
        }

        $jsonEnd = strrpos($response, '}');
        if ($jsonEnd === false) {
            throw new \Exception('Unterminated JSON in AI response: ' . substr($response, 0, 200));
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $parsed = json_decode($jsonString, true);

        if (is_array($parsed) && isset($parsed['steps'])) {
            return $this->sanitize($parsed);
        }

        $parsed = json_decode($response, true);
        if (is_array($parsed) && isset($parsed['steps'])) {
            return $this->sanitize($parsed);
        }

        throw new \Exception('Unable to extract valid JSON from AI pathway response: ' . substr($response, 0, 300));
    }

    private function sanitize(array $data): array
    {
        $today = now()->startOfDay();

        return [
            'overall_strategy' => $data['overall_strategy'] ?? 'A personalized strategy could not be generated. Please try again.',
            'steps' => array_map(function ($step) use ($today) {
                $date = $step['date'] ?? now()->addDays(7)->toDateString();

                try {
                    $parsedDate = \Carbon\Carbon::parse($date)->startOfDay();
                    if ($parsedDate->lessThan($today)) {
                        $date = now()->addDays(7)->toDateString();
                    }
                } catch (\Throwable) {
                    $date = now()->addDays(7)->toDateString();
                }

                return [
                    'label' => $step['label'] ?? 'Untitled step',
                    'date'  => $date,
                    'score' => isset($step['score']) ? max(1, min(100, (int) $step['score'])) : 0,
                ];
            }, $data['steps'] ?? []),
        ];
    }
}