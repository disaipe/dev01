<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $viewName = 'one_c_domain_users';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW `$this->viewName` AS (
            SELECT
                DISTINCT *
            FROM
                (
                    SELECT
                        `ad_user_entries`.`id`,
                        `ad_user_entries`.`username` AS `login`,
                        `ad_user_entries`.`name` AS `username`,
                        `ad_user_entries`.`company_prefix`,
                        `ad_user_entries`.`blocked`,
                        `info_base_count`
                    FROM `ad_user_entries`
                    INNER JOIN (
                        SELECT
                            DISTINCT `login`,
                            COUNT(1) as info_base_count
                        FROM `one_c_info_base_users`
                        WHERE
                            `login` IS NOT NULL
                            AND `domain` IS NOT NULL
                        GROUP BY `login`
                    ) AS `one_c_domain_users` ON `ad_user_entries`.`username` = `one_c_domain_users`.`login`
                    WHERE
                        `ad_user_entries`.`deleted_at` IS NULL
                ) AS `$this->viewName`
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
