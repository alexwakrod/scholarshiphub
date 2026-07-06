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
            Schema::create('scholarships', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->string('provider');
                $table->string('country');
                $table->decimal('amount', 10, 2)->nullable();
                $table->dateTime('deadline');
                $table->string('source_url')->nullable();
                $table->jsonb('parsed_requirements')->nullable();
                $table->enum('status', ['active', 'expired'])->default('active');
                $table->timestamps();

                $table->index('status');
                $table->index('deadline');
            });

            // Add generated tsvector for full‑text search on title and description
            DB::statement("ALTER TABLE scholarships ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (to_tsvector('english', coalesce(title, '') || ' ' || coalesce(description, ''))) STORED");
            DB::statement('CREATE INDEX scholarships_search_vector_idx ON scholarships USING GIN (search_vector)');

            // GIN index on parsed_requirements JSONB
            DB::statement('CREATE INDEX scholarships_parsed_requirements_gin ON scholarships USING GIN (parsed_requirements)');
        } catch (\Throwable $e) {
            Log::error('Migration scholarships failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('scholarships');
        } catch (\Throwable $e) {
            Log::error('Rollback scholarships failed: ' . $e->getMessage());
            throw $e;
        }
    }
};