<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiCache extends Model
{
    protected $table = 'ai_cache';

    protected $fillable = [
        'hash',
        'type',
        'input_data',
        'response_data',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'input_data'    => 'json',
            'response_data' => 'json',
            'expires_at'    => 'datetime',
        ];
    }
}