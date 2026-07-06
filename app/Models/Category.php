<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'order',
        'depth',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function scholarships(): HasMany
    {
        return $this->hasMany(Scholarship::class);
    }

    /**
     * Build a nested tree structure for the admin.
     */
    public static function getTree(): array
    {
        try {
            $roots = static::whereNull('parent_id')->orderBy('order')->get();
            return $roots->map(fn($root) => self::buildNode($root))->toArray();
        } catch (\Throwable $e) {
            Log::error('Category::getTree error: ' . $e->getMessage());
            return [];
        }
    }

    private static function buildNode($category): array
    {
        $children = $category->children;
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'depth' => $category->depth,
            'children' => $children->map(fn($child) => self::buildNode($child))->toArray(),
        ];
    }
}