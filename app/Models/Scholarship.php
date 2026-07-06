<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Scholarship extends Model
{
    protected $fillable = [
        'title',
        'description',
        'provider',
        'country',
        'amount',
        'deadline',
        'source_url',
        'parsed_requirements',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'amount' => 'decimal:2',
            'parsed_requirements' => 'json',
        ];
    }

    public function matchScores(): HasMany
    {
        return $this->hasMany(MatchScore::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope a query to only include active scholarships.
     */
    public function scopeActive($query)
    {
        try {
            return $query->where('status', 'active');
        } catch (\Throwable $e) {
            Log::error('Scholarship scopeActive error: ' . $e->getMessage());
            return $query;
        }
    }
}