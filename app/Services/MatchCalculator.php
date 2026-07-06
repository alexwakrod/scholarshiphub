<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\Scholarship;
use App\Models\MatchScore;
use Illuminate\Support\Facades\Log;

class MatchCalculator
{
    protected array $weights;

    /** @var FireworksAiClient|null */
    protected ?FireworksAiClient $aiClient = null;

    /** @var QwenCloudService|null */
    protected ?QwenCloudService $qwenService = null;

    /** AI‑powered strict eligibility verification – off by default to save tokens */
    protected bool $aiEligibilityVerificationEnabled;

    /** AI‑adjusted eligibility score weight when combining with algorithmic score */
    protected float $aiEligibilityWeight = 0.5;

    public function __construct()
    {
        // Heavier weight on eligibility, lighter on interests
        $this->weights = [
            'academic'    => 25,
            'demographic' => 25,
            'interest'    => 10,
            'eligibility' => 40,
        ];

        // Use dedicated config flag – off by default
        $this->aiEligibilityVerificationEnabled = config('scholarship.ai.eligibility_verification_enabled', false);

        if ($this->aiEligibilityVerificationEnabled) {
            try {
                if (!empty(config('services.fireworks.api_key'))) {
                    $this->aiClient = app(FireworksAiClient::class);
                }
                if (!empty(config('qwen_cloud.api_key'))) {
                    $this->qwenService = app(QwenCloudService::class);
                }
            } catch (\Throwable $e) {
                Log::warning('MatchCalculator: AI services not available, falling back to algorithmic scoring: ' . $e->getMessage());
                $this->aiEligibilityVerificationEnabled = false;
            }
        }
    }

    /**
     * Compute a detailed match score for a student profile and scholarship.
     */
    public function compute(StudentProfile $profile, Scholarship $scholarship): MatchScore
    {
        try {
            $academicScore    = $this->scoreAcademic($profile, $scholarship);
            $demographicScore = $this->scoreDemographic($profile, $scholarship);
            $interestScore    = $this->scoreInterest($profile, $scholarship);
            $eligibilityScore = $this->scoreEligibility($profile, $scholarship);

            // Optionally blend AI strict eligibility score
            if ($this->aiEligibilityVerificationEnabled) {
                $aiEligibility = $this->scoreEligibilityAI($profile, $scholarship);
                if ($aiEligibility !== null) {
                    $eligibilityScore = round(
                        ($eligibilityScore * (1 - $this->aiEligibilityWeight)) +
                        ($aiEligibility * $this->aiEligibilityWeight),
                        2
                    );
                }
            }

            $overall = round(
                ($academicScore    * $this->weights['academic']    / 100) +
                ($demographicScore * $this->weights['demographic'] / 100) +
                ($interestScore    * $this->weights['interest']    / 100) +
                ($eligibilityScore * $this->weights['eligibility'] / 100),
                2
            );

            // Hard cap: if eligibility is below 30%, overall cannot exceed 30%
            if ($eligibilityScore < 30) {
                $overall = min($overall, 30.0);
            }

            return MatchScore::updateOrCreate(
                [
                    'student_profile_id' => $profile->id,
                    'scholarship_id'     => $scholarship->id,
                ],
                [
                    'overall_score'   => $overall,
                    'category_scores' => [
                        'academic'    => $academicScore,
                        'demographic' => $demographicScore,
                        'interest'    => $interestScore,
                        'eligibility' => $eligibilityScore,
                    ],
                    'computed_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('MatchCalculator compute error: ' . $e->getMessage(), [
                'profile_id'     => $profile->id,
                'scholarship_id' => $scholarship->id,
            ]);
            throw $e;
        }
    }

    /**
     * Recompute all match scores for a given profile against active scholarships.
     */
    public function recomputeAllForProfile(StudentProfile $profile): int
    {
        $count = 0;
        Scholarship::active()->chunk(200, function ($scholarships) use ($profile, &$count) {
            foreach ($scholarships as $scholarship) {
                try {
                    $this->compute($profile, $scholarship);
                    $count++;
                } catch (\Throwable $e) {
                    Log::error('MatchCalculator recompute failed for scholarship ' . $scholarship->id . ': ' . $e->getMessage());
                }
            }
        });
        return $count;
    }

    // ================================================================
    // ACADEMIC SCORING (25% weight)
    // ================================================================
    protected function scoreAcademic(StudentProfile $profile, Scholarship $scholarship): float
    {
        $reqs = $scholarship->parsed_requirements;
        if (!$reqs) {
            return 50.0;
        }

        $levelScore = $this->scoreAcademicLevels($profile->academic_level, $reqs);
        $gpaScore   = $this->scoreGpa($profile->gpa, $reqs['minimum_gpa'] ?? null);
        $majorScore = $this->scoreMajor($profile->major, $reqs['majors'] ?? []);

        return round(($levelScore * 0.4) + ($gpaScore * 0.3) + ($majorScore * 0.3), 2);
    }

    /**
     * Academic level scoring: supports study_levels array or single academic_level.
     */
    protected function scoreAcademicLevels(?string $profileLevel, array $reqs): float
    {
        // Check if we have the new study_levels array
        $requiredLevels = $reqs['study_levels'] ?? null;
        if (is_array($requiredLevels) && !empty($requiredLevels)) {
            // If profile level matches any of the required levels, give full points
            if ($profileLevel && in_array($profileLevel, $requiredLevels)) {
                return 100.0;
            }
            // If no profile level, give moderate
            if (empty($profileLevel)) {
                return 40.0;
            }
            // Check compatibility: if profile level is higher than any required, still good
            foreach ($requiredLevels as $reqLevel) {
                if ($this->isLevelCompatible($profileLevel, $reqLevel)) {
                    return 85.0;
                }
            }
            // Partial
            return 30.0;
        }

        // Fallback to single academic_level
        $requiredLevel = $reqs['academic_level'] ?? null;
        if (empty($requiredLevel)) {
            return 100.0; // no requirement
        }
        if (empty($profileLevel)) {
            return 40.0;
        }
        $p = $this->levelHierarchy[strtolower($profileLevel)] ?? 0;
        $r = $this->levelHierarchy[strtolower($requiredLevel)] ?? 0;
        if ($p === $r) return 100.0;
        if ($p > $r) return 85.0;
        $diff = $r - $p;
        return max(0, 70 - ($diff * 30)); // stricter penalty
    }

    protected function scoreGpa(?float $profileGpa, ?float $minGpa): float
    {
        if ($minGpa === null) return 100.0;
        if ($profileGpa === null) return 30.0; // was 50.0
        if ($profileGpa >= $minGpa) return 100.0;
        $deficit = $minGpa - $profileGpa;
        return max(0, 80 - ($deficit * 30));
    }

    protected function scoreMajor(?string $profileMajor, array $eligibleMajors): float
    {
        if (empty($eligibleMajors)) return 100.0;
        if (in_array('Any', $eligibleMajors) || in_array('All', $eligibleMajors)) return 100.0;
        if (empty($profileMajor)) return 25.0; // was 50.0

        foreach ($eligibleMajors as $m) {
            if (strcasecmp($profileMajor, $m) === 0) return 100.0;
        }
        foreach ($eligibleMajors as $m) {
            if (stripos($profileMajor, $m) !== false || stripos($m, $profileMajor) !== false) return 90.0;
        }
        $profileGroup = $this->findMajorGroup($profileMajor);
        foreach ($eligibleMajors as $m) {
            if ($this->findMajorGroup($m) === $profileGroup) return 60.0;
        }
        return 20.0;
    }

    protected array $levelHierarchy = [
        'high_school'   => 1,
        'undergraduate' => 2,
        'bachelors'     => 2,
        'masters'       => 3,
        'phd'           => 4,
    ];

    protected function isLevelCompatible(string $profileLevel, string $requiredLevel): bool
    {
        $p = $this->levelHierarchy[strtolower($profileLevel)] ?? 0;
        $r = $this->levelHierarchy[strtolower($requiredLevel)] ?? 0;
        return $p >= $r;
    }

    protected function findMajorGroup(string $major): ?string
    {
        $majorLower = strtolower($major);
        foreach ($this->majorGroups as $group => $majors) {
            foreach ($majors as $m) {
                if (strtolower($m) === $majorLower || stripos($majorLower, strtolower($m)) !== false) {
                    return $group;
                }
            }
        }
        return null;
    }

    protected array $majorGroups = [
        'stem' => ['Computer Science', 'Engineering', 'Electrical Engineering', 'Mechanical Engineering', 'Civil Engineering', 'Chemical Engineering', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'Environmental Science', 'Geology', 'Technology', 'Science', 'Information Technology', 'Software Engineering', 'Data Science'],
        'medicine' => ['Medicine', 'Nursing', 'Pharmacy', 'Dentistry', 'Public Health', 'Medical', 'Health Sciences'],
        'business' => ['Business Administration', 'Accounting', 'Finance', 'Marketing', 'Economics', 'Business', 'Management', 'MBA'],
        'humanities' => ['Arts', 'History', 'Philosophy', 'English Literature', 'Linguistics', 'Political Science', 'Sociology', 'Anthropology', 'Psychology', 'Law', 'International Relations'],
        'education' => ['Education', 'Teaching'],
        'agriculture' => ['Agriculture', 'Food Science'],
    ];

    // ================================================================
    // DEMOGRAPHIC SCORING (25% weight)
    // ================================================================
    protected function scoreDemographic(StudentProfile $profile, Scholarship $scholarship): float
    {
        $reqs = $scholarship->parsed_requirements;
        if (!$reqs) return 50.0;

        $countryScore = $this->scoreCountry($profile->country, $reqs['countries'] ?? []);
        $ageScore     = $this->scoreAge($profile->date_of_birth, $reqs['age_range'] ?? null);
        $incomeScore  = $this->scoreIncome($profile->demographics, $reqs);
        $langScore    = $this->scoreLanguages($profile->languages, $reqs);

        return round(($countryScore * 0.5) + ($ageScore * 0.2) + ($incomeScore * 0.15) + ($langScore * 0.15), 2);
    }

    protected function scoreCountry(?string $profileCountry, array $eligibleCountries): float
    {
        if (empty($eligibleCountries)) return 100.0;
        if (empty($profileCountry)) return 20.0; // was 30.0
        foreach ($eligibleCountries as $c) {
            if (strcasecmp($profileCountry, $c) === 0) return 100.0;
        }
        return 0.0;
    }

    protected function scoreAge(?\DateTimeInterface $dob, ?array $ageRange): float
    {
        if (!$ageRange || !$dob) return 100.0;
        $age = now()->diffInYears($dob);
        $min = $ageRange['min'] ?? 0;
        $max = $ageRange['max'] ?? 99;
        if ($age >= $min && $age <= $max) return 100.0;
        $distance = min(abs($age - $min), abs($age - $max));
        return max(10, 100 - ($distance * 5));
    }

    protected function scoreIncome(?array $demographics, ?array $reqs): float
    {
        if (empty($demographics['income'])) return 50.0; // was 70.0
        $text = strtolower(implode(' ', array_merge($reqs['other_requirements'] ?? [], [$reqs['benefit_type'] ?? ''])));
        if (stripos($text, 'low income') !== false || stripos($text, 'need-based') !== false) {
            return strtolower($demographics['income']) === 'low' ? 100.0 : 30.0;
        }
        return 100.0;
    }

    protected function scoreLanguages(?array $profileLanguages, ?array $reqs): float
    {
        return 100.0; // simplified
    }

    // ================================================================
    // INTEREST SCORING (10% weight) – now requires at least 2 matching interests
    // ================================================================
    protected function scoreInterest(StudentProfile $profile, Scholarship $scholarship): float
    {
        $interests = $profile->interests ?? [];
        if (empty($interests)) return 50.0;

        $reqs = $scholarship->parsed_requirements;
        $scholarText = strtolower(
            $scholarship->title . ' ' .
            $scholarship->description . ' ' .
            implode(' ', $reqs['majors'] ?? []) . ' ' .
            implode(' ', $reqs['other_requirements'] ?? []) . ' ' .
            ($reqs['benefit_type'] ?? '')
        );

        $totalWeight = 0;
        $weightedScore = 0;
        $matchedCount = 0;

        $interestKeywords = [
            'fully_funded' => ['fully funded', 'full scholarship', 'full ride', 'tuition waiver'],
            'europe' => ['europe', 'european', 'eu'],
            'asia' => ['asia', 'asian'],
            'north_america' => ['north america', 'usa', 'united states', 'canada'],
            'engineering' => ['engineering', 'engineer'],
            'medicine' => ['medicine', 'medical', 'health', 'nursing', 'pharmacy'],
            'business' => ['business', 'management', 'finance', 'accounting', 'marketing', 'mba'],
            'arts' => ['art', 'design', 'music', 'film', 'media', 'architecture'],
            'technology' => ['technology', 'tech', 'computer', 'software', 'data', 'it', 'information technology'],
            'science' => ['science', 'physics', 'chemistry', 'biology', 'mathematics'],
            'humanities' => ['humanities', 'history', 'philosophy', 'literature', 'linguistics', 'psychology', 'sociology'],
            'law' => ['law', 'legal', 'international law'],
            'masters' => ['masters', 'master', 'msc', 'ma'],
            'phd' => ['phd', 'doctorate', 'doctoral'],
            'undergraduate' => ['undergraduate', 'bachelor', 'bsc', 'ba'],
            'research' => ['research', 'publication', 'thesis'],
            'internship' => ['internship', 'training'],
            'exchange' => ['exchange', 'exchange program'],
        ];

        foreach ($interests as $interest) {
            $keywords = $interestKeywords[$interest] ?? [$interest];
            $interestWeight = 1;
            $totalWeight += $interestWeight;
            $matchCount = 0;
            foreach ($keywords as $kw) {
                if (stripos($scholarText, $kw) !== false) $matchCount++;
            }
            $interestScore = count($keywords) > 0 ? min(1, $matchCount / count($keywords)) : 0;
            if ($interestScore > 0) $matchedCount++;
            $weightedScore += $interestScore * $interestWeight;
        }

        // Bonus for benefit_type matching user interest "fully_funded"
        if (in_array('fully_funded', $interests) && ($reqs['benefit_type'] ?? null)) {
            $benefitLower = strtolower($reqs['benefit_type']);
            if (stripos($benefitLower, 'fully funded') !== false) {
                $weightedScore += 1;
                $totalWeight += 1;
            }
        }

        $rawScore = $totalWeight > 0 ? round(($weightedScore / $totalWeight) * 100, 2) : 0;

        // If fewer than 2 interests matched, severely cap the score
        if ($matchedCount < 2 && count($interests) >= 2) {
            $rawScore = min($rawScore, 20.0);
        }
        return $rawScore;
    }

    // ================================================================
    // ELIGIBILITY SCORING (40% weight) – STRICT, WITH OPTIONAL AI VERIFICATION
    // ================================================================
    protected function scoreEligibility(StudentProfile $profile, Scholarship $scholarship): float
    {
        // Algorithmic baseline
        $reqs = $scholarship->parsed_requirements;
        if (!$reqs) return 50.0;

        $englishScore = $this->scoreEnglish($profile, $reqs['english_proficiency'] ?? null);
        $otherScore   = $this->scoreOtherRequirements($profile, $reqs['other_requirements'] ?? []);

        // Extra penalty for "nomination" or "national commission" – nearly impossible for a direct applicant
        $hasNomination = false;
        $lowerText = strtolower($scholarship->description ?? '');
        if (stripos($lowerText, 'national commission') !== false || stripos($lowerText, 'nomination') !== false) {
            $hasNomination = true;
            $otherScore = max(0, $otherScore - 60);
        }

        // Penalty for "competitive", "highly competitive", etc.
        if (stripos($lowerText, 'highly competitive') !== false || stripos($lowerText, 'competition') !== false) {
            $otherScore = max(0, $otherScore - 15);
        }

        return round(($englishScore * 0.4) + ($otherScore * 0.6), 2);
    }

    /**
     * Use AI (Fireworks or Qwen) to perform a strict eligibility assessment.
     * Returns a score 0‑100, or null if AI unavailable/fails.
     */
    protected function scoreEligibilityAI(StudentProfile $profile, Scholarship $scholarship): ?float
    {
        if (!$this->aiEligibilityVerificationEnabled) return null;

        try {
            $prompt = $this->buildAIStrictEligibilityPrompt($profile, $scholarship);

            // Prefer Qwen if available (faster rotation), else Fireworks
            if ($this->qwenService) {
                $response = $this->qwenService->sendChatRequestRotating('', $prompt, 300, 0.1);
            } elseif ($this->aiClient) {
                $response = $this->aiClient->complete($prompt, [
                    'temperature' => 0.1,
                    'max_tokens'  => 300,
                ]);
            } else {
                return null;
            }

            if ($response) {
                $score = $this->parseAIStrictScore($response);
                if ($score !== null) {
                    return max(0, min(100, $score));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('MatchCalculator AI eligibility failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Build a prompt that asks the AI to evaluate eligibility very strictly.
     */
    protected function buildAIStrictEligibilityPrompt(StudentProfile $profile, Scholarship $scholarship): string
    {
        $profileData = [
            'academic_level' => $profile->academic_level,
            'major'          => $profile->major,
            'gpa'            => $profile->gpa,
            'country'        => $profile->country,
            'age'            => $profile->date_of_birth ? now()->diffInYears($profile->date_of_birth) : null,
            'english'        => $profile->english_proficiency,
            'interests'      => $profile->interests ?? [],
            'languages'      => $profile->languages ?? [],
            'work_experience' => !empty($profile->demographics['has_work_experience']),
            'research_experience' => !empty($profile->demographics['has_research_experience']),
            'income'         => $profile->demographics['income'] ?? null,
        ];

        $scholarshipInfo = [
            'title'       => $scholarship->title,
            'description' => substr($scholarship->description, 0, 2000),
            'parsed_requirements' => $scholarship->parsed_requirements,
        ];

        $jsonProfile = json_encode($profileData, JSON_PRETTY_PRINT);
        $jsonScholarship = json_encode($scholarshipInfo, JSON_PRETTY_PRINT);

        return <<<EOT
You are a strict scholarship eligibility evaluator. Based on the following student profile and scholarship details, determine if the student is ELIGIBLE. Be extremely strict: any missing requirement should drastically lower the score. Consider hidden constraints like "nomination", "national commission", "highly competitive", etc.

Return ONLY a valid JSON object with a single key "score" (integer 0-100). 100 means perfectly eligible, 0 means completely ineligible.

Student Profile:
{$jsonProfile}

Scholarship:
{$jsonScholarship}
EOT;
    }

    /**
     * Parse the AI response to extract the strict eligibility score.
     */
    protected function parseAIStrictScore(string $response): ?int
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd   = strrpos($response, '}');
        if ($jsonStart !== false && $jsonEnd !== false) {
            $json = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $data = json_decode($json, true);
            if (isset($data['score']) && is_numeric($data['score'])) {
                return (int) $data['score'];
            }
        }
        // Fallback: try to find a number in the response
        if (preg_match('/\b(100|[1-9]?[0-9])\b/', $response, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    protected function scoreEnglish(StudentProfile $profile, ?string $requiredEnglish): float
    {
        if (empty($requiredEnglish)) return 100.0;
        $profileEnglish = $profile->english_proficiency;
        if (empty($profileEnglish)) return 40.0;

        preg_match('/(\d+\.?\d*)/', $requiredEnglish, $rMatch);
        preg_match('/(\d+\.?\d*)/', $profileEnglish, $pMatch);

        if (empty($pMatch) || empty($rMatch)) {
            return stripos($profileEnglish, $requiredEnglish) !== false ? 80.0 : 50.0;
        }

        $pNum = floatval($pMatch[1]);
        $rNum = floatval($rMatch[1]);

        $both = $profileEnglish . ' ' . $requiredEnglish;
        if (stripos($both, 'toefl') !== false) {
            return $pNum >= $rNum ? 100.0 : max(30, 100 - (($rNum - $pNum) * 2));
        } elseif (stripos($both, 'ielts') !== false) {
            return $pNum >= $rNum ? 100.0 : max(30, 100 - (($rNum - $pNum) * 20));
        } elseif (stripos($both, 'duolingo') !== false) {
            return $pNum >= $rNum ? 100.0 : max(30, 100 - (($rNum - $pNum) * 1.5));
        }
        return $pNum >= $rNum ? 90.0 : max(20, 80 - (($rNum - $pNum) * 10));
    }

    protected function scoreOtherRequirements(StudentProfile $profile, array $requirements): float
    {
        if (empty($requirements)) return 100.0;
        $total = count($requirements);
        $passed = 0;
        foreach ($requirements as $requirement) {
            if (empty(trim($requirement))) {
                $total--;
                continue;
            }
            if ($this->meetsRequirement($profile, $requirement)) $passed++;
        }
        return $total > 0 ? round(($passed / $total) * 100, 2) : 100.0;
    }

    protected function meetsRequirement(StudentProfile $profile, string $requirement): bool
    {
        $reqLower = strtolower($requirement);
        if (stripos($reqLower, 'work experience') !== false) {
            return !empty($profile->demographics['has_work_experience']);
        }
        if (stripos($reqLower, 'research experience') !== false || stripos($reqLower, 'publication') !== false) {
            return !empty($profile->demographics['has_research_experience']);
        }
        $searchable = strtolower(implode(' ', array_filter([
            $profile->bio, $profile->major, $profile->full_name,
            is_array($profile->interests) ? implode(' ', $profile->interests) : '',
            is_array($profile->languages) ? implode(' ', $profile->languages) : '',
        ])));
        if (stripos($searchable, $reqLower) !== false) return true;

        $keywords = preg_split('/\s+/', $reqLower);
        $meaningful = array_filter($keywords, fn($w) => strlen($w) > 2);
        if (empty($meaningful)) return false;
        $found = 0;
        foreach ($meaningful as $word) {
            if (stripos($searchable, $word) !== false) $found++;
        }
        // Stricter: require at least 80% of meaningful words to match
        return $found >= ceil(count($meaningful) * 0.8);
    }
}