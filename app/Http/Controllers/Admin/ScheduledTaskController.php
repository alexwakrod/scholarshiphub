<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\ScheduledTaskLog;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;

class ScheduledTaskController extends Controller
{
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

    public function run(Request $request)
    {
        $request->validate(['command' => 'required|string']);
        $commandName = $request->input('command');

        try {
            $log = ScheduledTaskLog::create([
                'command_name' => $commandName,
                'description'  => 'Manual run by admin',
                'status'       => 'running',
                'started_at'   => now(),
            ]);

            $exitCode = Artisan::call($commandName);
            $output   = Artisan::output();

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

    private function getScheduledCommands(): array
    {
        $commands = [];

        try {
            $schedule = app(Schedule::class);

            foreach ($schedule->events() as $event) {
                $rawCommand = $event->command ?? '';

                // Skip closures, execs, and non-Artisan events
                if (empty($rawCommand) || str_contains($rawCommand, 'Closure') || !str_contains($rawCommand, 'artisan')) {
                    continue;
                }

                // Extract the Artisan signature: everything after "artisan "
                $parts = explode('artisan ', $rawCommand, 2);
                $afterArtisan = isset($parts[1]) ? trim($parts[1]) : '';

                if (empty($afterArtisan)) {
                    continue;
                }

                // The signature is the first word (before any options)
                $signature = strtok($afterArtisan, ' ');

                // Get description from Artisan kernel
                $description = '';
                try {
                    $artisan = app(\Illuminate\Contracts\Console\Kernel::class);
                    $allCmds = $artisan->all();
                    if (isset($allCmds[$signature])) {
                        $description = $allCmds[$signature]->getDescription();
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
        } catch (\Throwable $e) {
            Log::error('getScheduledCommands error: ' . $e->getMessage());
        }

        // Fallback to hardcoded list if scheduler returns nothing
        if (empty($commands)) {
            $commands = [
                [
                    'signature'   => 'scholarships:expire',
                    'description' => 'Mark scholarships past their deadline as expired',
                    'cron'        => '0 1 * * *',
                ],
                [
                    'signature'   => 'reminders:deadlines',
                    'description' => 'Send deadline reminders for bookmarked scholarships due within 48 hours',
                    'cron'        => '0 * * * *',
                ],
                [
                    'signature'   => 'users:delete-scheduled',
                    'description' => 'Permanently delete accounts past their deletion grace period',
                    'cron'        => '0 3 * * *',
                ],
            ];

            foreach ($commands as &$cmd) {
                $cmd['nextRun'] = $this->getNextRun($cmd['cron']);
            }
        }

        return $commands;
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