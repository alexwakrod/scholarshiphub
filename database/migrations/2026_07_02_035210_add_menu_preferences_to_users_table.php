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
            Schema::table('users', function (Blueprint $table) {
                $table->jsonb('menu_preferences')->nullable();
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_menu_preferences failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('menu_preferences');
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_menu_preferences failed: ' . $e->getMessage());
            throw $e;
        }
    }
};