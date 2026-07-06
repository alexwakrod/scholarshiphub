<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    public $timestamps = false; // using created_at manually

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        try {
            return $query->whereNull('read_at');
        } catch (\Throwable $e) {
            Log::error('Notification scopeUnread error: ' . $e->getMessage());
            return $query;
        }
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        try {
            if (!$this->read_at) {
                $this->read_at = now();
                $this->save();
            }
        } catch (\Throwable $e) {
            Log::error('Notification markAsRead error: ' . $e->getMessage());
            throw $e;
        }
    }
}