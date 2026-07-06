<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ReviewStreamUpdate;
use Illuminate\Support\Facades\Log;

class TestPusher extends Command
{
    protected $signature = 'pusher:test {message}';
    protected $description = 'Send a test event synchronously with debugging';

    public function handle()
    {
        $message = $this->argument('message');

        $this->info('Dispatching event...');
        Log::info('TestPusher: attempting to broadcast event', ['message' => $message]);

        try {
            event(new ReviewStreamUpdate(0, $message));
            $this->info('Event dispatched successfully.');
            Log::info('TestPusher: event dispatched');
        } catch (\Throwable $e) {
            $this->error('Broadcast failed: ' . $e->getMessage());
            Log::error('TestPusher: broadcast failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return;
        }

        $this->info('If you see this, the event was sent to Pusher.');
    }
}