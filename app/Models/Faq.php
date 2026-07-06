<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Scope to published FAQs ordered by order column.
     */
    public function scopePublished($query)
    {
        try {
            return $query->where('is_published', true)->orderBy('order');
        } catch (\Throwable $e) {
            Log::error('Faq scopePublished error: ' . $e->getMessage());
            return $query;
        }
    }
}