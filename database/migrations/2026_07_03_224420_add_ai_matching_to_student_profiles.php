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
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->boolean('ai_matching_enabled')->default(true)->after('profile_completed');
            });
        } catch (\Throwable $e) {
            Log::error('Migration add_ai_matching_to_student_profiles failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->dropColumn('ai_matching_enabled');
            });
        } catch (\Throwable $e) {
            Log::error('Rollback add_ai_matching_to_student_profiles failed: ' . $e->getMessage());
            throw $e;
        }
    }
};