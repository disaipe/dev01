<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $viewName = 'one_c_user_info_base';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW `$this->viewName` AS (
            SELECT
                `ad_user_entries`.`id` AS `one_c_domain_user_id`,
                `one_c_domain_users`.`one_c_info_base_user_id`,
                `one_c_domain_users`.`one_c_info_base_id`
            FROM `ad_user_entries`
            INNER JOIN (
                SELECT
                    `id` AS `one_c_info_base_user_id`,
                    `login`,
                    `one_c_info_base_id`
                FROM `one_c_info_base_users`
                WHERE
                    `login` IS NOT NULL
                    AND `domain` IS NOT NULL
                )
                AS `one_c_domain_users` ON `ad_user_entries`.`username` = `one_c_domain_users`.`login`
            WHERE
                `ad_user_entries`.`blocked` != 1
                AND `ad_user_entries`.`deleted_at` IS NULL
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW `$this->viewName`");
    }
};
