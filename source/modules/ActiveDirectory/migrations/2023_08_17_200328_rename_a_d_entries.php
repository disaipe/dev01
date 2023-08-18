<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('ad_entries', 'ad_user_entries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('ad_user_entries', 'ad_entries');
    }
};
