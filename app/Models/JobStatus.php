<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $table = 'job_statuses';

    protected $fillable = [
        'job_uuid',
        'user_id',
        'type',
        'label',
        'status',
        'progress',
        'meta',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'meta'       => 'json',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}