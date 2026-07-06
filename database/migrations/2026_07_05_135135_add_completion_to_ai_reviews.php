<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->jsonb('completed_suggestions')->nullable();
            $table->jsonb('completed_grammar_issues')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->dropColumn(['completed_suggestions', 'completed_grammar_issues']);
        });
    }
};
