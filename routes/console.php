<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ExpireScholarships;
use App\Console\Commands\SendDeadlineReminders;
use App\Console\Commands\ScrapeScholarships;

Schedule::command(ExpireScholarships::class)->dailyAt('01:00');
Schedule::command(SendDeadlineReminders::class)->hourly();
