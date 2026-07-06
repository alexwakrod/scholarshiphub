<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ScheduledTaskLog;
use Illuminate\Support\Facades\Log;

class DeleteScheduledAccounts extends Command
{
    protected $signature = 'users:delete-scheduled';
    protected $description = 'Permanently delete user accounts past their deletion grace period';

    public function handle(): int
    {
        $logEntry = ScheduledTaskLog::create([
            'command_name' => $this->signature,
            'description'  => 'Automatic scheduled run',
            'status'       => 'running',
            'started_at'   => now(),
        ]);

        try {
            $usersToDelete = User::whereNotNull('deletion_scheduled_at')
                ->where('deletion_scheduled_at', '<=', now())
                ->get();

            $usersToDelete->each(function ($user) {
                Log::info('Deleting scheduled account.', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);
                $user->delete();
            });

            $deleted = $usersToDelete->count();

            Log::info('DeleteScheduledAccounts: deleted ' . $deleted . ' accounts.');
            $this->info("Deleted {$deleted} accounts.");

            $logEntry->update([
                'status'      => 'success',
                'output'      => "Deleted {$deleted} accounts.",
                'finished_at' => now(),
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('DeleteScheduledAccounts failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Failed to delete scheduled accounts: ' . $e->getMessage());

            $logEntry->update([
                'status'      => 'failed',
                'output'      => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return self::FAILURE;
        }
    }
}