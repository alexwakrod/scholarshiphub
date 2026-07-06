<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scholarship;
use App\Models\Bookmark;
use App\Models\Notification;
use App\Models\ScheduledTaskLog;
use Illuminate\Support\Facades\Log;

class SendDeadlineReminders extends Command
{
    protected $signature = 'reminders:deadlines';
    protected $description = 'Send deadline reminders to users for bookmarked scholarships due within 48 hours';

    public function handle(): int
    {
        $logEntry = ScheduledTaskLog::create([
            'command_name' => $this->signature,
            'description'  => 'Automatic scheduled run',
            'status'       => 'running',
            'started_at'   => now(),
        ]);

        try {
            $now = now();
            $windowStart = $now->copy();
            $windowEnd = $now->copy()->addHours(48);

            $scholarships = Scholarship::active()
                ->whereBetween('deadline', [$windowStart, $windowEnd])
                ->get();

            if ($scholarships->isEmpty()) {
                Log::info('SendDeadlineReminders: no upcoming deadlines in the next 48 hours.');
                $this->info('No upcoming deadlines.');

                $logEntry->update([
                    'status'      => 'success',
                    'output'      => 'No upcoming deadlines.',
                    'finished_at' => now(),
                ]);

                return self::SUCCESS;
            }

            $sent = 0;
            foreach ($scholarships as $scholarship) {
                $bookmarkUserIds = Bookmark::where('scholarship_id', $scholarship->id)
                    ->pluck('user_id');

                foreach ($bookmarkUserIds as $userId) {
                    $alreadySent = Notification::where('user_id', $userId)
                        ->where('type', 'deadline_reminder')
                        ->where('data->scholarship_id', $scholarship->id)
                        ->where('created_at', '>=', $now->subHours(48))
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }

                    Notification::create([
                        'user_id' => $userId,
                        'type'    => 'deadline_reminder',
                        'data'    => [
                            'message'        => "Reminder: The scholarship '{$scholarship->title}' deadline is on {$scholarship->deadline->toDateString()}.",
                            'scholarship_id' => $scholarship->id,
                            'deadline'       => $scholarship->deadline->toDateTimeString(),
                        ],
                    ]);
                    $sent++;
                }
            }

            Log::info("SendDeadlineReminders: sent {$sent} reminders.");
            $this->info("Sent {$sent} reminders.");

            $logEntry->update([
                'status'      => 'success',
                'output'      => "Sent {$sent} reminders.",
                'finished_at' => now(),
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('SendDeadlineReminders failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('Failed to send reminders.');

            $logEntry->update([
                'status'      => 'failed',
                'output'      => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return self::FAILURE;
        }
    }
}