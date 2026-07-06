<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use App\Enums\ApplicationStatus;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'scholarship_id',
        'status',
        'checklist',
        'notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'checklist' => 'json',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Calculate the progress percentage of completed checklist items.
     */
    public function checklistProgress(): float
    {
        try {
            $tasks = $this->checklist;
            if (!is_array($tasks) || count($tasks) === 0) {
                return 0.0;
            }
            $completed = count(array_filter($tasks, fn ($task) => $task['completed'] ?? false));
            return round(($completed / count($tasks)) * 100, 2);
        } catch (\Throwable $e) {
            Log::error('Application checklistProgress error: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Mark a checklist task as completed or not.
     */
    public function updateTask(string $taskName, bool $completed): void
    {
        try {
            $tasks = $this->checklist ?? [];
            $tasks = array_map(function ($task) use ($taskName, $completed) {
                if (($task['task'] ?? '') === $taskName) {
                    $task['completed'] = $completed;
                }
                return $task;
            }, $tasks);
            $this->checklist = $tasks;
            $this->save();
        } catch (\Throwable $e) {
            Log::error('Application updateTask error: ' . $e->getMessage());
            throw $e;
        }
    }
}