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
            Schema::create('dashboard_widgets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('widget_id'); // unique key identifying the widget type, e.g. 'stats', 'top_matches', 'radar_chart'
                $table->string('title')->nullable();
                $table->jsonb('config')->nullable(); // any widget-specific config
                $table->integer('position_x')->default(0);
                $table->integer('position_y')->default(0);
                $table->integer('width')->default(1);
                $table->integer('height')->default(1);
                $table->boolean('visible')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'widget_id']);
                $table->index('user_id');
            });
        } catch (\Throwable $e) {
            Log::error('Migration dashboard_widgets failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('dashboard_widgets');
        } catch (\Throwable $e) {
            Log::error('Rollback dashboard_widgets failed: ' . $e->getMessage());
            throw $e;
        }
    }
};