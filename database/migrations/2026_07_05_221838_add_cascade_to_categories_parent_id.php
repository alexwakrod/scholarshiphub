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
            // Drop the existing foreign key that uses nullOnDelete
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
            });

            // Recreate the foreign key with cascade on delete
            Schema::table('categories', function (Blueprint $table) {
                $table->foreign('parent_id')
                      ->references('id')
                      ->on('categories')
                      ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_cascade_to_categories_parent_id failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
            });

            Schema::table('categories', function (Blueprint $table) {
                $table->foreign('parent_id')
                      ->references('id')
                      ->on('categories')
                      ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_cascade_to_categories_parent_id failed: ' . $e->getMessage());
            throw $e;
        }
    }
};