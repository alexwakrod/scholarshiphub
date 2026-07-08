<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('shared_dashboards');
    }

    public function down(): void
    {
        // Re‑create only if necessary; leave empty for now
    }
};