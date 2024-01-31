<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $viewName = 'atlanta_users_summary';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW `$this->viewName` AS (
            SELECT
                aue.id,
                aue.company_prefix,
                aue.username,
                aue.name,
                aue.department,
                aue.post,
                aue.blocked,

                (
                    aue.groups LIKE '%RemoteTerminalUsers%'
                    or aue.groups LIKE '%remote_terminal_admin%'
                ) as 'has_rdp',

                aue.sip_enabled as 'has_sip',
                aue.groups LIKE '%vpn_clients%' as 'has_vpn',

                case
                    when du.id is null then false
                    else true
                end as 'has_directum',

                COALESCE(ocdu.info_base_count, 0) > 0 as 'has_onec'
            FROM ad_user_entries aue
            LEFT JOIN directum_users du on du.name = aue.username
            LEFT JOIN one_c_domain_users ocdu on ocdu.login = aue.username
            WHERE
                aue.blocked = 0
                and aue.deleted_at is null
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
