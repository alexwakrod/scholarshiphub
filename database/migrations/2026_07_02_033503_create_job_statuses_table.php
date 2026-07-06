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
            Schema::create('job_statuses', function (Blueprint $table) {
                $table->id();
                $table->uuid('job_uuid')->unique();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type')->nullable();
                $table->string('label')->nullable();
                $table->string('status')->default('pending');
                $table->integer('progress')->default(0);
                $table->jsonb('meta')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('status');
            });
        } catch (\Throwable $e) {
            Log::error('Migration job_statuses failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('job_statuses');
        } catch (\Throwable $e) {
            Log::error('Rollback job_statuses failed: ' . $e->getMessage());
            throw $e;
        }
    }
};