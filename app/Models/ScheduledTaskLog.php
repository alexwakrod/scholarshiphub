<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskLog extends Model
{
    protected $table = 'scheduled_task_logs';

    protected $fillable = [
        'command_name',
        'description',
        'status',
        'output',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}