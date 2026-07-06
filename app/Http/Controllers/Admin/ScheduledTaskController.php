<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use App\Models\ScheduledTaskLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\CommandEvent;
use Cron\CronExpression;

class ScheduledTaskController extends Controller
{
    /**
     * Display all scheduled tasks and their execution history.
     */
    public function index(Request $request)
    {
        try {
            $commands = $this->getScheduledCommands();

            $logs = ScheduledTaskLog::orderBy('started_at', 'desc')
                ->paginate(20)
                ->withQueryString();

            return Inertia::render('Admin/ScheduledTasks/Index', [
                'commands' => $commands,
                'logs'     => $logs,
                'filters'  => $request->only(['command', 'status']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\ScheduledTaskController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/ScheduledTasks/Index', ['commands' => [], 'logs' => []]);
        }
    }

    /**
     * Manually run a scheduled command.
     */
    public function run(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $commandName = $request->input('command');

        try {
            // Create log entry
            $log = ScheduledTaskLog::create([
                'command_name' => $commandName,
                'description'  => 'Manual run by admin',
                'status'       => 'running',
                'started_at'   => now(),
            ]);

            // Execute the command
            $exitCode = Artisan::call($commandName);
            $output = Artisan::output();

            // Update log
            $log->update([
                'status'      => $exitCode === 0 ? 'success' : 'failed',
                'output'      => $output,
                'finished_at' => now(),
            ]);

            Log::info("Manual command '{$commandName}' executed with exit code {$exitCode}.");

            return response()->json(['message' => 'Command executed.', 'status' => $log->status]);
        } catch (\Throwable $e) {
            Log::error('Manual command execution failed: ' . $e->getMessage(), ['command' => $commandName]);
            return response()->json(['message' => 'Execution failed.'], 500);
        }
    }

    /**
     * Extract all scheduled commands from the Laravel scheduler.
     */
    private function getScheduledCommands(): array
    {
        try {
            $schedule = app(Schedule::class);
            $commands = [];

            foreach ($schedule->events() as $event) {
                // Only include command events that have an identifiable artisan command
                if (!$event instanceof CommandEvent) {
                    continue;
                }

                $signature = $event->command ?? null;
                if (!$signature) {
                    continue;
                }

                // Determine the description by checking the application's console commands
                $description = '';
                $command = null;
                try {
                    $artisan = app(\Illuminate\Contracts\Console\Kernel::class);
                    $allCommands = $artisan->all();
                    if (isset($allCommands[$signature])) {
                        $command = $allCommands[$signature];
                        $description = $command->getDescription();
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

                $expression = $event->expression;
                $nextRun = $this->getNextRun($expression);

                $commands[] = [
                    'signature'   => $signature,
                    'description' => $description ?: 'No description available',
                    'cron'        => $expression,
                    'nextRun'     => $nextRun,
                ];
            }

            return $commands;
        } catch (\Throwable $e) {
            Log::error('getScheduledCommands error: ' . $e->getMessage());
            return [];
        }
    }

    private function getNextRun(string $cronExpression): string
    {
        try {
            $cron = new CronExpression($cronExpression);
            return $cron->getNextRunDate()->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return 'unknown';
        }
    }
}