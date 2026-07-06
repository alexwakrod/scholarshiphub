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
            Schema::table('import_jobs', function (Blueprint $table) {
                $table->jsonb('column_mapping')->nullable(); // e.g. {"email":"email","name":"name"}
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_column_mapping failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('import_jobs', function (Blueprint $table) {
                $table->dropColumn('column_mapping');
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_column_mapping failed: ' . $e->getMessage());
            throw $e;
        }
    }
};