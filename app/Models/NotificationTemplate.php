<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $table = 'notification_templates';

    protected $fillable = [
        'name',
        'subject',
        'body_text',
        'body_html',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'json',
            'is_active' => 'boolean',
        ];
    }
}