<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class SurveyResponse extends Model
{
    protected $fillable = [
        'user_id',
        'interested',
        'want_ai_feedback',
        'willing_to_pay',
        'extra_data',
    ];

    public $timestamps = false; // created_at only

    protected function casts(): array
    {
        return [
            'interested' => 'boolean',
            'want_ai_feedback' => 'boolean',
            'willing_to_pay' => 'boolean',
            'extra_data' => 'json',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retrieve aggregated survey stats for display.
     */
    public static function getStats(): array
    {
        try {
            $total = static::count();
            if ($total === 0) {
                return [];
            }
            return [
                'interested_percent' => round((static::where('interested', true)->count() / $total) * 100, 1),
                'want_ai_feedback_percent' => round((static::where('want_ai_feedback', true)->count() / $total) * 100, 1),
                'willing_to_pay_percent' => round((static::where('willing_to_pay', true)->count() / $total) * 100, 1),
            ];
        } catch (\Throwable $e) {
            Log::error('SurveyResponse getStats error: ' . $e->getMessage());
            return [];
        }
    }
}