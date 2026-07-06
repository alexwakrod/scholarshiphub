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
            Schema::create('scraping_selectors', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->unique();
                $table->string('container_selector');
                $table->string('title_selector');
                $table->string('link_selector')->nullable();
                $table->string('description_selector')->nullable();
                $table->string('deadline_selector')->nullable();
                $table->string('provider_selector')->nullable();
                $table->string('country_selector')->nullable();
                $table->jsonb('extra_selectors')->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();
            });
        } catch (\Throwable $e) {
            Log::error('Migration scraping_selectors failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scraping_selectors');
    }
};