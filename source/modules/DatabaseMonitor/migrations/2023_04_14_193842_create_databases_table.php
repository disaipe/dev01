<?php

use App\Modules\DatabaseMonitor\Models\DatabaseServer;
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
        Schema::create('databases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DatabaseServer::class)->constrained()->cascadeOnDelete();
            $table->string('dbid');
            $table->string('name', 64);
            $table->unsignedBigInteger('size');
            $table->string('company_code', 16)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('databases');
    }
};
