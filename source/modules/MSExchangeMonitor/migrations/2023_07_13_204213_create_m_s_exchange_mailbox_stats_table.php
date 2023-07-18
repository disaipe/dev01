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
        Schema::create('ms_exchange_mailbox_stats', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 64);
            $table->string('display_name', 256)->nullable();
            $table->unsignedBigInteger('total_item_size')->default(0);
            $table->unsignedInteger('total_item_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_exchange_mailbox_stats');
    }
};
