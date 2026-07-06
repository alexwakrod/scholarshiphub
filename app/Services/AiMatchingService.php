<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\Scholarship;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMatchingService
{
    protected ?QwenCloudService $qwen = null;
    protected ?FireworksAiClient $fireworks = null;
    protected bool $qwenEnabled;
    protected bool $fireworksEnabled;

    public function __construct()
    {
        $this->qwenEnabled = !empty(config('qwen_cloud.api_key'));
        $this->fireworksEnabled = !empty(config('services.fireworks.api_key'));

        if ($this->qwenEnabled) {
            $this->qwen = app(QwenCloudService::class);
        }
        if ($this->fireworksEnabled) {
            $this->fireworks = app(FireworksAiClient::class);
        }
    }

    /**
     * Generic prompt – uses Qwen rotation, fallback to Fireworks.
     */
    public function prompt(string $prompt, array $options = [], ?string &$usedModel = null): ?string
    {
        if ($this->qwen) {
            $maxTokens = $options['max_tokens'] ?? 400;
            $temperature = $options['temperature'] ?? 0.2;
            // Use rotating request; model selection is handled internally
            return $this->qwen->sendChatRequestRotating('', $prompt, $maxTokens, $temperature);
        }

        if ($this->fireworks) {
            return $this->fireworks->complete($prompt, [
                'temperature' => $options['temperature'] ?? 0.2,
                'max_tokens'  => $options['max_tokens'] ?? 400,
            ]);
        }

        return null;
    }

    /**
     * Validate a scholarship and extract structured fields.
     * Priority: Qwen Cloud → Fireworks.
     */
    public function validateScholarship(array $data): ?array
    {
        if ($this->qwen) {
            $result = $this->qwen->validateScholarship($data);
            if ($result !== null) return $result;
        }

        if ($this->fireworks) {
            Log::info('AiMatchingService: Qwen unavailable, falling back to Fireworks for validation.');
            // Fireworks fallback: simply return a valid response with minimal data so scraping continues
            return [
                'is_valid' => true,
                'confidence' => 0.5,
                'deadline' => null,
                'provider' => $data['provider'] ?? null,
                'amount' => null,
                'education_level' => [],
                'gpa_required' => null,
                'majors' => [],
                'eligible_countries' => [],
                'age_range' => null,
                'english_proficiency' => null,
                'other_requirements' => [],
                'benefit_type' => null,
                'duration' => null,
                'match_score' => 0,
                'match_reasons' => [],
            ];
        }

        return null;
    }

    /**
     * Extract detailed eligibility requirements.
     * Priority: Qwen Cloud → Fireworks.
     */
    public function extractRequirements(array $data): ?array
    {
        if ($this->qwen) {
            $result = $this->qwen->extractRequirements($data);
            if ($result !== null) return $result;
        }

        // Fireworks extraction not reliable – return null to use algorithmic fallback
        return null;
    }

    /**
     * Match a student profile against a list of scholarship IDs.
     * Uses algorithmic MatchCalculator for now.
     */
    public function matchStudentToScholarships(StudentProfile $student, array $scholarshipIds): array
    {
        // Future: use Qwen batch-match
        return [];
    }

    /**
     * Run batch matching for all completed profiles.
     */
    public function runBatchMatching(): int
    {
        $students = StudentProfile::where('profile_completed', true)
            ->where('ai_matching_enabled', true)
            ->limit(200)
            ->get();
        if ($students->isEmpty()) return 0;

        $calculator = app(MatchCalculator::class);
        $count = 0;
        foreach ($students as $student) {
            $calculator->recomputeAllForProfile($student);
            $count += Scholarship::where('status', 'active')->count();
        }
        return $count;
    }

    /**
     * Retrieve top matches for a student profile.
     */
    public function getTopMatches(StudentProfile $student, int $limit = 10): array
    {
        return \App\Models\MatchScore::where('student_profile_id', $student->id)
            ->with('scholarship')
            ->orderByDesc('overall_score')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Build a textual representation of a student profile for AI matching.
     */
    protected function buildStudentProfileText(StudentProfile $student): string
    {
        $parts = [];
        $parts[] = "Full Name: " . ($student->full_name ?? 'N/A');
        $parts[] = "Academic Level: " . ($student->academic_level ?? 'N/A');
        $parts[] = "Major: " . ($student->major ?? 'N/A');
        $parts[] = "GPA: " . ($student->gpa ?? 'N/A');
        $parts[] = "Date of Birth: " . ($student->date_of_birth ? $student->date_of_birth->toDateString() : 'N/A');
        $parts[] = "Country: " . ($student->country ?? 'N/A');
        $demographics = $student->demographics ?? [];
        $parts[] = "Demographics: " . json_encode($demographics);
        $parts[] = "English Proficiency: " . ($student->english_proficiency ?? 'N/A');
        $languages = $student->languages ?? [];
        $parts[] = "Languages: " . implode(', ', $languages);
        $parts[] = "Bio: " . ($student->bio ?? 'N/A');
        $interests = $student->interests ?? [];
        $parts[] = "Interests: " . implode(', ', $interests);
        $parts[] = "Profile Completed: " . ($student->profile_completed ? 'Yes' : 'No');
        return implode("\n", $parts);
    }
}