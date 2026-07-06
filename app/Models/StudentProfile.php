<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'academic_level',
        'major',
        'gpa',
        'date_of_birth',
        'country',
        'demographics',
        'interests',
        'english_proficiency',
        'languages',
        'bio',
        'profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'date_of_birth' => 'date',
            'demographics' => 'json',
            'interests' => 'json',
            'languages' => 'json',
            'profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchScores(): HasMany
    {
        return $this->hasMany(MatchScore::class);
    }
}