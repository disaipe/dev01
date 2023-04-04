<?php

use App\Core\Enums\JobProtocolState;
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
        $states = Arr::map(JobProtocolState::cases(), fn (JobProtocolState $i) => $i->value);

        Schema::create('job_protocols', function (Blueprint $table) use ($states) {
            $table->id();
            $table->uuid();
            $table->string('name', 256);
            $table->string('description', 512)->nullable();
            $table->string('module', 64)->nullable();
            $table->enum('state', $states);
            $table->jsonb('result')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_protocols');
    }
};
