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
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->text('question');
                $table->text('answer');
                $table->integer('order')->default(0);
                $table->boolean('is_published')->default(true);
                $table->timestamps();

                $table->index('order');
                $table->index('is_published');
            });
        } catch (\Throwable $e) {
            Log::error('Migration faqs failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('faqs');
        } catch (\Throwable $e) {
            Log::error('Rollback faqs failed: ' . $e->getMessage());
            throw $e;
        }
    }
};