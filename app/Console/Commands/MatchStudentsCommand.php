<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiMatchingService;
use App\Models\StudentProfile;
use App\Models\AiMatch;

class MatchStudentsCommand extends Command
{
    protected $signature = 'scholarships:match-students
                            {--batch : Run batch matching for all eligible students}
                            {--student= : Match a single student by ID}
                            {--threshold=0.6 : Minimum match score (0.0-1.0)}';
    protected $description = 'Match student profiles to scholarships using the local AI service';

    public function handle(): int
    {
        $service = app(AiMatchingService::class);

        if ($this->option('student')) {
            return $this->matchSingle($service, (int) $this->option('student'));
        }

        if ($this->option('batch')) {
            return $this->runBatch($service);
        }

        $this->error('Specify --batch for all students or --student={ID} for a single student.');
        return self::FAILURE;
    }

    private function matchSingle(AiMatchingService $service, int $studentId): int
    {
        $profile = StudentProfile::find($studentId);
        if (!$profile) {
            $this->error("Student profile with ID {$studentId} not found.");
            return self::FAILURE;
        }

        if (!$profile->profile_completed) {
            $this->warn('Student profile is not completed. Matching may be less accurate.');
        }

        $scholarshipIds = \App\Models\Scholarship::where('status', 'active')
            ->where('deadline', '>', now())
            ->pluck('id')
            ->toArray();

        if (empty($scholarshipIds)) {
            $this->warn('No active scholarships found.');
            return self::SUCCESS;
        }

        $this->info("Matching student {$profile->full_name} against " . count($scholarshipIds) . " scholarships...");
        $matches = $service->matchStudentToScholarships($profile, $scholarshipIds);

        $this->info('Matches found: ' . count($matches));
        foreach ($matches as $sid => $data) {
            $this->line("  Scholarship ID {$sid}: Score {$data['score']} - " . implode(', ', $data['reasons']));
        }

        return self::SUCCESS;
    }

    private function runBatch(AiMatchingService $service): int
    {
        $this->info('Starting batch matching...');
        $count = $service->runBatchMatching();
        $this->info("Batch matching complete. {$count} match records created/updated.");
        return self::SUCCESS;
    }
}