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
        Schema::create('sharepoint_lists', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 512);
            $table->string('list_name', 512);
            $table->string('list_site', 63)->nullable();
            $table->foreignIdFor(\App\Models\CustomReference::class)->constrained();
            $table->jsonb('options')->nullable();
            $table->boolean('enabled')->default(false);
            $table->dateTime('last_sync')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sharepoint_lists');
    }
};
