<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class MatchScore extends Model
{
    protected $fillable = [
        'student_profile_id',
        'scholarship_id',
        'overall_score',
        'category_scores',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_score'   => 'decimal:2',
            'category_scores' => 'json',
            'computed_at'     => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    /**
     * Required by DashboardController for eager loading.
     */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function getCategoryScore(string $category): ?float
    {
        try {
            $scores = $this->category_scores;
            if (is_array($scores) && array_key_exists($category, $scores)) {
                return (float) $scores[$category];
            }
            return null;
        } catch (\Throwable $e) {
            Log::error('MatchScore getCategoryScore error: ' . $e->getMessage());
            return null;
        }
    }
}