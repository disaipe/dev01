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
        Schema::create('ad_computer_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name', '256');
            $table->string('operating_system', '256')->nullable();
            $table->string('operating_system_version', 64)->nullable();
            $table->string('dns_name', 64)->nullable();
            $table->string('ou_path', 4096)->nullable();
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_computer_entries');
    }
};
