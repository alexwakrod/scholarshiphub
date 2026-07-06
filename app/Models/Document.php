<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'file_type',
        'file_path',
        'file_name',
        'text_extracted',
        'version_number',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiReviews(): HasMany
    {
        return $this->hasMany(AiReview::class);
    }

    /**
     * Get the latest AI review for this document.
     */
    public function latestReview(): HasOne
    {
        return $this->hasOne(AiReview::class)->latestOfMany('reviewed_at');
    }

    /**
     * Scope by file type.
     */
    public function scopeOfType($query, string $type)
    {
        try {
            return $query->where('file_type', $type);
        } catch (\Throwable $e) {
            Log::error('Document scopeOfType error: ' . $e->getMessage());
            return $query;
        }
    }
}