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
        Schema::table('one_c_info_base_users', function (Blueprint $table) {
            $table->unique(['one_c_info_base_id', 'login', 'domain']);

            $table->dropSoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_c_info_base_users', function (Blueprint $table) {
            $table->dropUnique('one_c_info_base_users_one_c_info_base_id_login_domain_unique');

            $table->softDeletes();
        });
    }
};
