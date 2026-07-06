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
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action'); // 'created', 'updated', 'deleted', 'login', 'logout', 'impersonation_start', 'impersonation_stop', etc.
                $table->string('subject_type')->nullable(); // model class
                $table->unsignedBigInteger('subject_id')->nullable(); // model id
                $table->jsonb('properties')->nullable(); // old values, new values, diff
                $table->text('description')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'created_at']);
                $table->index(['subject_type', 'subject_id']);
                $table->index('action');
            });
        } catch (\Throwable $e) {
            Log::error('Migration activity_logs failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('activity_logs');
        } catch (\Throwable $e) {
            Log::error('Rollback activity_logs failed: ' . $e->getMessage());
            throw $e;
        }
    }
};