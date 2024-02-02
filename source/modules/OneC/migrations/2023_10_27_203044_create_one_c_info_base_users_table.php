<?php

use App\Modules\OneC\Models\OneCInfoBase;
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
        Schema::create('one_c_info_base_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OneCInfoBase::class)->constrained()->cascadeOnDelete();
            $table->string('username', 128);
            $table->string('login', 32)->nullable();
            $table->string('domain', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_c_info_base_users');
    }
};
