<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::create('student_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('full_name');
                $table->string('academic_level')->nullable();
                $table->string('major')->nullable();
                $table->decimal('gpa', 3, 2)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('country')->nullable();
                $table->jsonb('demographics')->nullable();
                $table->jsonb('interests')->nullable();
                $table->string('english_proficiency')->nullable();
                $table->jsonb('languages')->nullable();
                $table->text('bio')->nullable();
                $table->boolean('profile_completed')->default(false);
                $table->timestamps();

                $table->index('user_id');
                $table->index('profile_completed');
            });

            // Create GIN indexes after the table is committed
            DB::statement('CREATE INDEX IF NOT EXISTS student_profiles_demographics_gin ON student_profiles USING GIN (demographics)');
            DB::statement('CREATE INDEX IF NOT EXISTS student_profiles_interests_gin ON student_profiles USING GIN (interests)');
        } catch (\Throwable $e) {
            Log::error('Migration student_profiles failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('student_profiles');
        } catch (\Throwable $e) {
            Log::error('Rollback student_profiles failed: ' . $e->getMessage());
            throw $e;
        }
    }
};