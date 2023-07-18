<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->string('code', 256)->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('indicator_code', 256)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // nothing
    }
};
