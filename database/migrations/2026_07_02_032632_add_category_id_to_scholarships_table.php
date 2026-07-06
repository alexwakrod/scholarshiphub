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
            Schema::table('scholarships', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('provider')->constrained('categories')->nullOnDelete();
                $table->index('category_id');
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_category_id_to_scholarships failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('scholarships', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_category_id_to_scholarships failed: ' . $e->getMessage());
            throw $e;
        }
    }
};