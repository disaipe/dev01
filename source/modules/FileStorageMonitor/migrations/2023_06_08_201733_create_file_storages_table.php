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
        Schema::create('file_storages', function (Blueprint $table) {
            $table->id();
            $table->string('company_code', 16)->nullable();
            $table->string('name', 256);
            $table->string('path', 1024);
            $table->unsignedBigInteger('size')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('exclude')->default(false);
            $table->dateTime('last_sync')->nullable();
            $table->float('last_duration')->nullable();
            $table->string('last_status', 1)->nullable();
            $table->string('last_error', 1024)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_storages');
    }
};
