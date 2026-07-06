<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomReport extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'model',
        'fields',
        'chart_type',
        'filters',
        'aggregation',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'fields'      => 'json',
            'filters'     => 'json',
            'aggregation' => 'json',
            'is_public'   => 'boolean',
        ];
    }
}