<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Bookmark extends Model
{
    protected $table = 'bookmarks';

    protected $fillable = [
        'user_id',
        'scholarship_id',
    ];

    // Composite primary key – no auto‑increment
    public $incrementing = false;
    protected $primaryKey = null;

    // Disable automatic timestamps – the table only has created_at
    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id');
    }

    public static function toggle(int $userId, int $scholarshipId): bool
    {
        try {
            $exists = static::where('user_id', $userId)
                            ->where('scholarship_id', $scholarshipId)
                            ->exists();
            if ($exists) {
                static::where('user_id', $userId)
                      ->where('scholarship_id', $scholarshipId)
                      ->delete();
                return false;
            }
            static::create([
                'user_id' => $userId,
                'scholarship_id' => $scholarshipId,
                // 'created_at' will be handled by the database default
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Bookmark toggle error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'scholarship_id' => $scholarshipId,
            ]);
            throw $e;
        }
    }
}