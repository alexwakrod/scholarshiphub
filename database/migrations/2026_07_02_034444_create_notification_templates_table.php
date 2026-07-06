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
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('subject');
                $table->text('body_text')->nullable();
                $table->text('body_html')->nullable();
                $table->jsonb('variables')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } catch (\Throwable $e) {
            Log::error('Migration notification_templates failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('notification_templates');
        } catch (\Throwable $e) {
            Log::error('Rollback notification_templates failed: ' . $e->getMessage());
            throw $e;
        }
    }
};