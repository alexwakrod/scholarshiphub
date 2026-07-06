<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Testimonial extends Model
{
    protected $fillable = [
        'user_id',
        'quote',
        'name_display',
        'grade',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to approved testimonials.
     */
    public function scopeApproved($query)
    {
        try {
            return $query->where('is_approved', true);
        } catch (\Throwable $e) {
            Log::error('Testimonial scopeApproved error: ' . $e->getMessage());
            return $query;
        }
    }

    /**
     * Approve a testimonial by admin.
     */
    public function approve(): void
    {
        try {
            $this->is_approved = true;
            $this->save();
        } catch (\Throwable $e) {
            Log::error('Testimonial approve error: ' . $e->getMessage());
            throw $e;
        }
    }
}