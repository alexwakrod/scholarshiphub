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
            Schema::create('chart_annotations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('chart_id'); // e.g. 'registrations_chart'
                $table->decimal('position_x', 8, 2); // percentage 0‑100
                $table->decimal('position_y', 8, 2); // percentage 0‑100
                $table->text('note');
                $table->timestamps();

                $table->index(['chart_id', 'user_id']);
            });
        } catch (\Throwable $e) {
            Log::error('Migration chart_annotations failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('chart_annotations');
        } catch (\Throwable $e) {
            Log::error('Rollback chart_annotations failed: ' . $e->getMessage());
            throw $e;
        }
    }
};