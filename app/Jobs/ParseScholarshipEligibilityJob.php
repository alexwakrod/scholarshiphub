<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Scholarship;
use App\Services\FireworksAiClient;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\Admin\ScholarshipParseFailed;

class ParseScholarshipEligibilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;

    private Scholarship $scholarship;

    public function __construct(Scholarship $scholarship)
    {
        $this->scholarship = $scholarship;
        $this->onQueue('ai');
    }

    public function handle(FireworksAiClient $aiClient): void
    {
        try {
            $description = $this->scholarship->description;
            if (empty(trim($description))) {
                Log::warning('ParseScholarshipEligibilityJob: empty description for scholarship ' . $this->scholarship->id);
                return;
            }

            $prompt = $this->buildPrompt($description);

            $response = $aiClient->complete($prompt, [
                'temperature' => 0.2,
                'max_tokens' => 1500,
            ]);

            $parsed = $this->parseResponse($response);

            $this->scholarship->update([
                'parsed_requirements' => $parsed,
            ]);

            Log::info('ParseScholarshipEligibilityJob completed.', [
                'scholarship_id' => $this->scholarship->id,
                'parsed_keys' => array_keys($parsed),
            ]);
        } catch (\Throwable $e) {
            Log::error('ParseScholarshipEligibilityJob failed: ' . $e->getMessage(), [
                'scholarship_id' => $this->scholarship->id,
                'trace' => $e->getTraceAsString(),
            ]);

            // Notify admin if this is the last attempt
            if ($this->attempts() >= $this->tries) {
                try {
                    $admins = \App\Models\User::where('role', 'admin')->get();
                    NotificationFacade::send($admins, new ScholarshipParseFailed($this->scholarship));
                } catch (\Throwable $notifyError) {
                    Log::error('Failed to notify admins of parse failure: ' . $notifyError->getMessage());
                }
            }

            throw $e; // allow retry if attempts remain
        }
    }

    private function buildPrompt(string $description): string
    {
        $prompt = <<<EOT
You are an expert scholarship eligibility parser. Given the following scholarship description, extract the eligibility criteria into a structured JSON object.

Return ONLY valid JSON with these keys (use null for missing values):
- academic_level: string or null (e.g., "undergraduate", "masters", "phd")
- minimum_gpa: number or null (e.g., 3.0)
- majors: array of strings or empty array
- countries: array of eligible country names or empty array
- age_range: object with "min" and "max" integers, or null
- english_proficiency: string or null (e.g., "IELTS 6.5")
- other_requirements: array of strings or empty array

Description:
{$description}
EOT;
        return $prompt;
    }

    private function parseResponse(string $response): array
    {
        // Try to extract JSON from response (some models wrap in markdown)
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed = json_decode($jsonString, true);
            if (is_array($parsed)) {
                return $this->sanitize($parsed);
            }
        }

        // If direct decode fails, attempt full response decode
        $parsed = json_decode($response, true);
        if (is_array($parsed)) {
            return $this->sanitize($parsed);
        }

        throw new \Exception('Unable to extract valid JSON from AI response: ' . substr($response, 0, 200));
    }

    private function sanitize(array $data): array
    {
        return [
            'academic_level' => $data['academic_level'] ?? null,
            'minimum_gpa' => isset($data['minimum_gpa']) ? (float) $data['minimum_gpa'] : null,
            'majors' => is_array($data['majors'] ?? null) ? $data['majors'] : [],
            'countries' => is_array($data['countries'] ?? null) ? $data['countries'] : [],
            'age_range' => (isset($data['age_range']) && is_array($data['age_range']))
                ? ['min' => (int) ($data['age_range']['min'] ?? 0), 'max' => (int) ($data['age_range']['max'] ?? 99)]
                : null,
            'english_proficiency' => $data['english_proficiency'] ?? null,
            'other_requirements' => is_array($data['other_requirements'] ?? null) ? $data['other_requirements'] : [],
        ];
    }
}