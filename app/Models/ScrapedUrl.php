<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapedUrl extends Model
{
    protected $fillable = ['url', 'last_scraped_at'];

    protected function casts(): array
    {
        return [
            'last_scraped_at' => 'datetime',
        ];
    }

    /**
     * Check if the URL was scraped within the given number of days.
     */
    public static function isRecentlyScraped(string $url, int $days = 14): bool
    {
        $record = static::where('url', $url)->first();
        if (!$record) return false;
        return $record->last_scraped_at->diffInDays(now()) < $days;
    }

    /**
     * Mark a URL as scraped now (update or create).
     */
    public static function markScraped(string $url): void
    {
        static::updateOrCreate(
            ['url' => $url],
            ['last_scraped_at' => now()]
        );
    }
}