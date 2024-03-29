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
        Schema::create('custom_references', function (Blueprint $table) {
            $table->id();
            $table->string('display_name', 128);
            $table->string('name', 32);
            $table->string('label', 128);
            $table->string('plural_label', 128);
            $table->boolean('company_context')->default(false);
            $table->boolean('enabled')->default(false);
            $table->jsonb('schema');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_references');
    }
};
