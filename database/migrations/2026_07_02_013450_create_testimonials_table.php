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
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('quote');
                $table->string('name_display');
                $table->string('grade')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->timestamps();

                $table->index('is_approved');
            });
        } catch (\Throwable $e) {
            Log::error('Migration testimonials failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('testimonials');
        } catch (\Throwable $e) {
            Log::error('Rollback testimonials failed: ' . $e->getMessage());
            throw $e;
        }
    }
};