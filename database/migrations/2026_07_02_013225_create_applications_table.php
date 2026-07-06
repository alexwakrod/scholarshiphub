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
            Schema::create('applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->enum('status', ['interested', 'applied', 'submitted', 'accepted', 'rejected'])
                      ->default('interested');
                $table->jsonb('checklist')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'scholarship_id']);
                $table->index('user_id');
                $table->index('scholarship_id');
                $table->index('status');
            });
        } catch (\Throwable $e) {
            Log::error('Migration applications failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('applications');
        } catch (\Throwable $e) {
            Log::error('Rollback applications failed: ' . $e->getMessage());
            throw $e;
        }
    }
};