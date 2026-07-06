<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'scholarship_id',
        'rating',
        'comment',
    ];

    public $timestamps = false; // created_at only

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Scope approved only (for public display). Here all reviews are considered approved unless otherwise feature added.
     */
    public function scopeApproved($query)
    {
        try {
            // If an approval column existed we would filter; currently all are considered approved.
            return $query;
        } catch (\Throwable $e) {
            Log::error('Review scopeApproved error: ' . $e->getMessage());
            return $query;
        }
    }

    /**
     * Average rating for a scholarship.
     */
    public static function averageRating(int $scholarshipId): float
    {
        try {
            return (float) static::where('scholarship_id', $scholarshipId)->avg('rating') ?? 0.0;
        } catch (\Throwable $e) {
            Log::error('Review averageRating error: ' . $e->getMessage());
            return 0.0;
        }
    }
}