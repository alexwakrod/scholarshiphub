<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class AiReview extends Model
{
    protected $fillable = [
        'document_id',
        'quality_score',
        'ats_score',
        'competitiveness_score',
        'strengths',
        'weaknesses',
        'suggestions',
        'grammar_issues',
        'reviewed_at',
        'completed_suggestions',
        'completed_grammar_issues',
    ];

    protected function casts(): array
    {
        return [
            'quality_score'           => 'decimal:1',
            'ats_score'               => 'decimal:1',
            'competitiveness_score'   => 'decimal:1',
            'strengths'               => 'json',
            'weaknesses'              => 'json',
            'suggestions'             => 'json',
            'grammar_issues'          => 'json',
            'reviewed_at'             => 'datetime',
            'completed_suggestions'   => 'json',
            'completed_grammar_issues'=> 'json',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get strength list as array (with fallback).
     */
    public function getStrengths(): array
    {
        try {
            return is_array($this->strengths) ? $this->strengths : [];
        } catch (\Throwable $e) {
            Log::error('AiReview getStrengths error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get weakness list as array.
     */
    public function getWeaknesses(): array
    {
        try {
            return is_array($this->weaknesses) ? $this->weaknesses : [];
        } catch (\Throwable $e) {
            Log::error('AiReview getWeaknesses error: ' . $e->getMessage());
            return [];
        }
    }
}