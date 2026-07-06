<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // e.g. 'deadline_reminder', 'new_match', 'review_completed'
                $table->jsonb('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'read_at']);
                $table->index('type');
            });
        } catch (\Throwable $e) {
            Log::error('Migration notifications failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('notifications');
        } catch (\Throwable $e) {
            Log::error('Rollback notifications failed: ' . $e->getMessage());
            throw $e;
        }
    }
};