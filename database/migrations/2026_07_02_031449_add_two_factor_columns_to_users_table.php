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
                $table->string('two_factor_secret')->nullable();
                $table->boolean('two_factor_enabled')->default(false);
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_two_factor_columns failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['two_factor_secret', 'two_factor_enabled']);
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_two_factor_columns failed: ' . $e->getMessage());
            throw $e;
        }
    }
};