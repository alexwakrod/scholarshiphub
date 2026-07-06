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
            Schema::create('bookmarks', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->primary(['user_id', 'scholarship_id']);
            });
        } catch (\Throwable $e) {
            Log::error('Migration bookmarks failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('bookmarks');
        } catch (\Throwable $e) {
            Log::error('Rollback bookmarks failed: ' . $e->getMessage());
            throw $e;
        }
    }
};