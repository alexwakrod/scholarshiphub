<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMatch extends Model
{
    protected $table = 'ai_matches';

    protected $fillable = [
        'student_profile_id',
        'scholarship_id',
        'match_score',
        'match_reasons',
        'student_match_data',
        'status',
        'matched_at',
    ];

    protected function casts(): array
    {
        return [
            'match_score'        => 'decimal:4',
            'match_reasons'      => 'json',
            'student_match_data' => 'json',
            'matched_at'         => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}