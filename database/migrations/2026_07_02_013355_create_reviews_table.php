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
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->smallInteger('rating')->unsigned(); // 1-5
                $table->text('comment')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['user_id', 'scholarship_id']);
            });
        } catch (\Throwable $e) {
            Log::error('Migration reviews failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('reviews');
        } catch (\Throwable $e) {
            Log::error('Rollback reviews failed: ' . $e->getMessage());
            throw $e;
        }
    }
};