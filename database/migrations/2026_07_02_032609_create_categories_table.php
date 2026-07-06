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
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->integer('order')->default(0);
                $table->integer('depth')->default(0);
                $table->timestamps();
                $table->index('parent_id');
                $table->index('order');
            });
        } catch (\Throwable $e) {
            Log::error('Migration categories failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('categories');
        } catch (\Throwable $e) {
            Log::error('Rollback categories failed: ' . $e->getMessage());
            throw $e;
        }
    }
};