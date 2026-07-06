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
            Schema::create('ai_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
                $table->decimal('quality_score', 3, 1)->nullable();
                $table->decimal('ats_score', 3, 1)->nullable();
                $table->decimal('competitiveness_score', 3, 1)->nullable();
                $table->jsonb('strengths')->nullable();
                $table->jsonb('weaknesses')->nullable();
                $table->jsonb('suggestions')->nullable();
                $table->jsonb('grammar_issues')->nullable();
                $table->timestamp('reviewed_at')->useCurrent();
                $table->timestamps();

                $table->index('document_id');
            });
        } catch (\Throwable $e) {
            Log::error('Migration ai_reviews failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('ai_reviews');
        } catch (\Throwable $e) {
            Log::error('Rollback ai_reviews failed: ' . $e->getMessage());
            throw $e;
        }
    }
};