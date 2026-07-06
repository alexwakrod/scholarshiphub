<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scholarship;
use App\Models\ScheduledTaskLog;
use Illuminate\Support\Facades\Log;

class ExpireScholarships extends Command
{
    protected $signature = 'scholarships:expire';
    protected $description = 'Mark scholarships past their deadline as expired';

    public function handle(): int
    {
        $logEntry = ScheduledTaskLog::create([
            'command_name' => $this->signature,
            'description'  => 'Automatic scheduled run',
            'status'       => 'running',
            'started_at'   => now(),
        ]);

        try {
            $count = Scholarship::where('status', 'active')
                ->where('deadline', '<', now())
                ->update(['status' => 'expired']);

            Log::info('ExpireScholarships: marked ' . $count . ' scholarships as expired.');
            $this->info("Expired {$count} scholarships.");

            $logEntry->update([
                'status'      => 'success',
                'output'      => "Expired {$count} scholarships.",
                'finished_at' => now(),
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('ExpireScholarships failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Failed to expire scholarships: ' . $e->getMessage());

            $logEntry->update([
                'status'      => 'failed',
                'output'      => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return self::FAILURE;
        }
    }
}