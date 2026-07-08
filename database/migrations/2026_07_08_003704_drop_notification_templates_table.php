<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('notification_templates');
    }

    public function down(): void
    {
        // Re‑create only if absolutely necessary; otherwise leave empty
    }
};