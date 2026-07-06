<?php

use Illuminate\Support\Facades\Broadcast;
use App\Console\Commands\DeleteScheduledAccounts;

Broadcast::channel('document-review.{id}', function ($user, $id) {
    return true; // public channel – no authentication required
});
Schedule::command(DeleteScheduledAccounts::class)->dailyAt('03:00');