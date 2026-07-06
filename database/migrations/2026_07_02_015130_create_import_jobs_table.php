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
            Schema::create('import_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_path');
                $table->string('status')->default('pending'); // pending, processing, completed, failed
                $table->integer('total_rows')->default(0);
                $table->integer('processed_rows')->default(0);
                $table->integer('failed_rows')->default(0);
                $table->jsonb('error_messages')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index('status');
            });
        } catch (\Throwable $e) {
            Log::error('Migration import_jobs failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('import_jobs');
        } catch (\Throwable $e) {
            Log::error('Rollback import_jobs failed: ' . $e->getMessage());
            throw $e;
        }
    }
};