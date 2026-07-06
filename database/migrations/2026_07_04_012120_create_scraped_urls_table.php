<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048)->unique();
            $table->timestamp('last_scraped_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_urls');
    }
};