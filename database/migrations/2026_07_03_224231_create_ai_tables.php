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
            Schema::create('ai_cache', function (Blueprint $table) {
                $table->id();
                $table->string('hash', 64)->unique();
                $table->string('type', 50)->index();            // e.g. 'scholarship_validation', 'scholarship_requirements'
                $table->jsonb('input_data');
                $table->jsonb('response_data');
                $table->timestamp('expires_at')->index();
                $table->timestamps();
            });
        } catch (\Throwable $e) {
            Log::error('Migration ai_cache failed: ' . $e->getMessage());
            throw $e;
        }

        try {
            Schema::create('ai_matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
                $table->foreignId('scholarship_id')->constrained('scholarships')->cascadeOnDelete();
                $table->decimal('match_score', 5, 4)->default(0);  // e.g. 0.8543
                $table->jsonb('match_reasons')->nullable();
                $table->jsonb('student_match_data')->nullable();
                $table->string('status', 30)->default('pending');
                $table->timestamp('matched_at')->nullable();
                $table->timestamps();

                $table->unique(['student_profile_id', 'scholarship_id']);
                $table->index('match_score');
                $table->index('status');
            });
        } catch (\Throwable $e) {
            Log::error('Migration ai_matches failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_matches');
        Schema::dropIfExists('ai_cache');
    }
};