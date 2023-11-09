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
        Schema::create('one_c_info_bases', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->string('conn_string', 512)->nullable();
            $table->string('server',256)->nullable();
            $table->string('ref', 128)->nullable();
            $table->string('list_path',1024);
            $table->string('db_type', 64)->nullable();
            $table->string('db_server', 128)->nullable();
            $table->string('db_base', 128)->nullable();
            $table->timestamps();

            $table->unique(['server', 'ref']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_c_info_bases');
    }
};
