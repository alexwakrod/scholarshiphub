<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\StudentProfile;
use App\Services\MatchCalculator;
use App\Services\AiMatchingService;

class RecomputeMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;
    public $tries = 1;

    private int $profileId;

    public function __construct(int $profileId)
    {
        $this->profileId = $profileId;
        $this->onQueue('default');
    }

    public function handle(MatchCalculator $calculator, AiMatchingService $aiService): void
    {
        try {
            $profile = StudentProfile::find($this->profileId);
            if (!$profile) {
                Log::warning('RecomputeMatchesJob: profile not found: ' . $this->profileId);
                return;
            }

            if (!$profile->profile_completed) {
                Log::info('RecomputeMatchesJob: profile not completed, skipping.', ['profile_id' => $this->profileId]);
                return;
            }

            // 1. Algorithmic recomputation
            $count = $calculator->recomputeAllForProfile($profile);
            Log::info('RecomputeMatchesJob: algorithmic recomputation done.', [
                'profile_id' => $this->profileId,
                'scholarships_processed' => $count,
            ]);

            // 2. AI‑powered batch matching (if service is enabled)
            if (config('ai_service.enabled', false)) {
                Log::info('RecomputeMatchesJob: starting AI matching for profile.', ['profile_id' => $this->profileId]);
                try {
                    $scholarshipIds = \App\Models\Scholarship::where('status', 'active')
                        ->where('deadline', '>', now())
                        ->pluck('id')
                        ->toArray();

                    if (!empty($scholarshipIds)) {
                        $matches = $aiService->matchStudentToScholarships($profile, $scholarshipIds);
                        foreach ($matches as $sid => $data) {
                            \App\Models\AiMatch::updateOrCreate(
                                [
                                    'student_profile_id' => $profile->id,
                                    'scholarship_id'     => $sid,
                                ],
                                [
                                    'match_score'        => $data['score'],
                                    'match_reasons'      => $data['reasons'] ?? [],
                                    'student_match_data' => $data['matching_fields'] ?? [],
                                    'status'             => 'matched',
                                    'matched_at'         => now(),
                                ]
                            );
                        }
                        Log::info('RecomputeMatchesJob: AI matching completed.', [
                            'profile_id' => $this->profileId,
                            'ai_matches' => count($matches),
                        ]);
                    }
                } catch (\Throwable $aiException) {
                    Log::warning('RecomputeMatchesJob: AI matching failed, using algorithmic scores only. ' . $aiException->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('RecomputeMatchesJob failed: ' . $e->getMessage(), [
                'profile_id' => $this->profileId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}