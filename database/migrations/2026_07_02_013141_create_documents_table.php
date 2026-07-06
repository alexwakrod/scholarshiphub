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
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('file_type', ['cv', 'personal_statement', 'other']);
                $table->string('file_path');
                $table->string('file_name');
                $table->text('text_extracted')->nullable();
                $table->integer('version_number')->default(1);
                $table->timestamps();

                $table->unique(['user_id', 'file_type', 'version_number']);
                $table->index('user_id');
                $table->index('file_type');
            });
        } catch (\Throwable $e) {
            Log::error('Migration documents failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('documents');
        } catch (\Throwable $e) {
            Log::error('Rollback documents failed: ' . $e->getMessage());
            throw $e;
        }
    }
};