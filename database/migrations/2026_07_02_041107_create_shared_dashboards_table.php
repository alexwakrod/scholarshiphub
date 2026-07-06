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
            Schema::create('shared_dashboards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->jsonb('widget_ids'); // array of widget_id strings to show
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->index('token');
            });
        } catch (\Throwable $e) {
            Log::error('Migration shared_dashboards failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('shared_dashboards');
        } catch (\Throwable $e) {
            Log::error('Rollback shared_dashboards failed: ' . $e->getMessage());
            throw $e;
        }
    }
};