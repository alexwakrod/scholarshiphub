<?php
// database/migrations/xxxx_xx_xx_add_deletion_scheduled_at_to_users_table.php

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
                $table->timestamp('deletion_scheduled_at')->nullable()->after('remember_token');
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_deletion_scheduled_at failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('deletion_scheduled_at');
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_deletion_scheduled_at failed: ' . $e->getMessage());
            throw $e;
        }
    }
};