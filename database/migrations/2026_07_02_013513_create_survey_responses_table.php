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
            Schema::create('survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->boolean('interested')->nullable();
                $table->boolean('want_ai_feedback')->nullable();
                $table->boolean('willing_to_pay')->nullable();
                $table->jsonb('extra_data')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('user_id');
            });
        } catch (\Throwable $e) {
            Log::error('Migration survey_responses failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('survey_responses');
        } catch (\Throwable $e) {
            Log::error('Rollback survey_responses failed: ' . $e->getMessage());
            throw $e;
        }
    }
};