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
        Schema::create('ad_computer_entry_status', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('ad_computer_entry_id')
                ->references('id')
                ->on('ad_computer_entries')
                ->cascadeOnDelete();

            $table->string('username', 128);
            $table->dateTime('synced_at')->index()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_computer_entry_status');
    }
};
