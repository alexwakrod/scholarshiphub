<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SharedDashboard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'token',
        'widget_ids',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'widget_ids' => 'json',
            'is_active'  => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(32);
            }
        });
    }
}