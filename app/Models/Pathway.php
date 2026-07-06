<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Pathway extends Model
{
    protected $fillable = [
        'user_id',
        'generated_at',
        'ai_explanation',
        'milestone_sequence',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'milestone_sequence' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retrieve milestones as an array with fallback.
     */
    public function getMilestones(): array
    {
        try {
            return is_array($this->milestone_sequence) ? $this->milestone_sequence : [];
        } catch (\Throwable $e) {
            Log::error('Pathway getMilestones error: ' . $e->getMessage());
            return [];
        }
    }
}