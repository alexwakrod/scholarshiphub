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
            Schema::create('pathways', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('generated_at')->useCurrent();
                $table->text('ai_explanation')->nullable();
                $table->jsonb('milestone_sequence')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('generated_at');
            });
        } catch (\Throwable $e) {
            Log::error('Migration pathways failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('pathways');
        } catch (\Throwable $e) {
            Log::error('Rollback pathways failed: ' . $e->getMessage());
            throw $e;
        }
    }
};