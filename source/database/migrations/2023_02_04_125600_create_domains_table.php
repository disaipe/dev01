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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('code', 16);
            $table->string('host', 64);
            $table->integer('port');
            $table->string('username', 128)->nullable();
            $table->string('password', 256)->nullable();
            $table->string('base_dn', 512)->nullable();
            $table->integer('timeout')->default(5);
            $table->boolean('ssl')->default(false);
            $table->boolean('tls')->default(false);
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
