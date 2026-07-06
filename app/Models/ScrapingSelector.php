<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapingSelector extends Model
{
    protected $table = 'scraping_selectors';

    protected $fillable = [
        'domain',
        'container_selector',
        'title_selector',
        'link_selector',
        'description_selector',
        'deadline_selector',
        'provider_selector',
        'country_selector',
        'extra_selectors',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'extra_selectors' => 'json',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Retrieve saved selectors for a domain, or null.
     */
    public static function forDomain(string $domain): ?self
    {
        return static::where('domain', $domain)->first();
    }
}