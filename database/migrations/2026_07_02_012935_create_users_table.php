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
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->enum('role', ['student', 'admin'])->default('student');
                $table->string('avatar_url')->nullable();
                $table->string('locale', 2)->default('en');
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index('email');
            });

            // Add generated tsvector column for full‑text search on name and email
            DB::statement('ALTER TABLE users ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (to_tsvector(\'english\', coalesce(name, \'\') || \' \' || coalesce(email, \'\'))) STORED');
            DB::statement('CREATE INDEX users_search_vector_idx ON users USING GIN (search_vector)');
        } catch (\Throwable $e) {
            Log::error('Migration users failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('users');
        } catch (\Throwable $e) {
            Log::error('Rollback users failed: ' . $e->getMessage());
            throw $e;
        }
    }
};