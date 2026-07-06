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
            Schema::create('custom_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('model'); // 'Scholarship', 'User'
                $table->jsonb('fields'); // selected fields
                $table->string('chart_type')->nullable(); // 'bar', 'line', 'table'
                $table->jsonb('filters')->nullable(); // { date_from, date_to, status, etc. }
                $table->jsonb('aggregation')->nullable(); // { type: 'count'|'sum'|'avg', field: string }
                $table->boolean('is_public')->default(false);
                $table->timestamps();

                $table->index('user_id');
            });
        } catch (\Throwable $e) {
            Log::error('Migration custom_reports failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('custom_reports');
        } catch (\Throwable $e) {
            Log::error('Rollback custom_reports failed: ' . $e->getMessage());
            throw $e;
        }
    }
};