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
            Schema::create('match_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
                $table->foreignId('scholarship_id')->constrained('scholarships')->cascadeOnDelete();
                $table->decimal('overall_score', 5, 2); // 0.00 - 100.00
                $table->jsonb('category_scores'); // { academic, demographic, interest, eligibility }
                $table->timestamp('computed_at')->useCurrent();
                $table->timestamps();

                $table->unique(['student_profile_id', 'scholarship_id']);
                $table->index('student_profile_id');
                $table->index('scholarship_id');
                $table->index('overall_score');
            });
        } catch (\Throwable $e) {
            Log::error('Migration match_scores failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('match_scores');
        } catch (\Throwable $e) {
            Log::error('Rollback match_scores failed: ' . $e->getMessage());
            throw $e;
        }
    }
};