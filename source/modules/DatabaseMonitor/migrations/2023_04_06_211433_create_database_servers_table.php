<?php

use App\Modules\DatabaseMonitor\Enums\DatabaseServerStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statuses = Arr::map(DatabaseServerStatus::cases(), fn (DatabaseServerStatus $i) => $i->value);

        Schema::create('database_servers', function (Blueprint $table) use ($statuses) {
            $table->id();
            $table->string('type', 16);
            $table->string('name', 128);
            $table->string('host', 64);
            $table->integer('port')->nullable();
            $table->string('username', 64)->nullable();
            $table->string('password', 256)->nullable();
            $table->string('options', 1024)->nullable();
            $table->boolean('monitor')->default(false);
            $table->enum('last_status', $statuses)->nullable();
            $table->datetime('last_check')->nullable();
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
        Schema::dropIfExists('database_servers');
    }
};
