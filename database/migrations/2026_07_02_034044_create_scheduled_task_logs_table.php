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
            Schema::create('scheduled_task_logs', function (Blueprint $table) {
                $table->id();
                $table->string('command_name'); // e.g. 'scholarships:expire'
                $table->string('description')->nullable();
                $table->enum('status', ['running', 'success', 'failed'])->default('running');
                $table->text('output')->nullable();
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index('command_name');
                $table->index('status');
                $table->index('started_at');
            });
        } catch (\Throwable $e) {
            Log::error('Migration scheduled_task_logs failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('scheduled_task_logs');
        } catch (\Throwable $e) {
            Log::error('Rollback scheduled_task_logs failed: ' . $e->getMessage());
            throw $e;
        }
    }
};