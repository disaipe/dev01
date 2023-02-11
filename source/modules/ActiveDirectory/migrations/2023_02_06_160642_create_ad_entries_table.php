<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ad_entries', function (Blueprint $table) {
            $table->id();
            $table->string('company_prefix', 16)->nullable();
            $table->string('company_name', 256)->nullable();
            $table->string('username', 64);
            $table->string('name', 256);
            $table->string('department', 256)->nullable();
            $table->string('post', 256)->nullable();
            $table->string('email', 128)->nullable();
            $table->string('ou_path', 4096)->nullable();
            $table->text('groups')->nullable();
            $table->dateTime('last_logon')->nullable();
            $table->integer('logon_count')->nullable();
            $table->integer('state')->nullable();
            $table->boolean('sip_enabled')->default(false);
            $table->boolean('blocked')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_entries');
    }
};
