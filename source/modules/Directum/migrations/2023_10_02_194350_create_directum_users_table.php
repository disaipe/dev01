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
        Schema::create('directum_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('domain', 32)->nullable();
            $table->timestamps();

            $table->unique(['name', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directum_users');
    }
};
