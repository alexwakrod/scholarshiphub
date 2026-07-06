<?php

namespace App\Traits;

use App\Models\JobStatus;
use Illuminate\Support\Str;

trait TracksProgress
{
    protected ?JobStatus $jobStatusRecord = null;

    /**
     * Begin tracking progress for this job.
     *
     * @param int|null $userId
     * @param string   $type   e.g. 'AiReview', 'Import', 'PathwayGeneration'
     * @param string   $label  Human‑readable label for the notification.
     */
    protected function startTracking(?int $userId, string $type, string $label = 'Job'): void
    {
        try {
            $this->jobStatusRecord = JobStatus::create([
                'job_uuid'   => (string) Str::uuid(),
                'user_id'    => $userId,
                'type'       => $type,
                'label'      => $label,
                'status'     => 'processing',
                'progress'   => 0,
                'started_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Update progress percentage and optionally the status string.
     */
    protected function updateProgress(int $progress, ?string $status = null): void
    {
        if (!$this->jobStatusRecord) return;
        try {
            $data = ['progress' => $progress];
            if ($status) {
                $data['status'] = $status;
            }
            $this->jobStatusRecord->update($data);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Mark the job as completed.
     */
    protected function markCompleted(): void
    {
        if (!$this->jobStatusRecord) return;
        try {
            $this->jobStatusRecord->update([
                'status'      => 'completed',
                'progress'    => 100,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Mark the job as failed with an error message.
     */
    protected function markFailed(string $errorMessage = ''): void
    {
        if (!$this->jobStatusRecord) return;
        try {
            $this->jobStatusRecord->update([
                'status'      => 'failed',
                'finished_at' => now(),
                'meta'        => array_merge($this->jobStatusRecord->meta ?? [], ['error' => $errorMessage]),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}