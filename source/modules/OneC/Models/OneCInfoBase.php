<?php

namespace App\Modules\OneC\Models;

use App\Attributes\Lazy;
use App\Core\Reference\ReferenceModel;
use App\Core\Traits\ExtendSelectQuery;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use App\Modules\DatabaseMonitor\Models\Database;
use App\Modules\DatabaseMonitor\Models\DatabaseServer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * @property string list_path
 * @property string name
 * @property string conn_string
 * @property string server
 * @property string ref
 * @property string db_type
 * @property string db_server
 * @property string db_base
 * @property string folder
 * @property Database database
 */
class OneCInfoBase extends ReferenceModel
{
    use ExtendSelectQuery, WithoutSoftDeletes;

    protected $fillable = [
        'list_path',
        'name',
        'conn_string',
        'server',
        'ref',
        'db_type',
        'db_server',
        'db_base',
        'folder',
    ];

    #[Lazy]
    public function domain_users(): BelongsToMany
    {
        return $this->belongsToMany(
            OneCDomainUser::class,
            'one_c_user_info_base',
            'one_c_info_base_id',
            'one_c_domain_user_id'
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_code', 'code');
    }

    public function database(): BelongsTo
    {
        return $this->belongsTo(Database::class, 'database_id');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->where('company_code', '=', $code);
    }

    protected function extendSelect(Builder $builder): Builder
    {
        /** @var Database $databasesInstance */
        $databasesInstance = app(Database::class);
        $databasesTable = $databasesInstance->getTable();

        /** @var DatabaseServer $serversInstance */
        $serversInstance = app(DatabaseServer::class);
        $serversTable = $serversInstance->getTable();

        return $builder
            ->getModel()
            ->newModelQuery()
            ->join(
                $serversTable,
                $serversInstance->qualifyColumn('deleted_at'),
                'IS',
                DB::raw("
                        NULL
                        AND (
                            {$serversInstance->qualifyColumn('host')} = {$builder->qualifyColumn('db_server')}
                            OR {$serversInstance->qualifyColumn('aliases')} LIKE CONCAT('%', REPLACE({$builder->qualifyColumn('db_server')}, '\\\\', '\\\\\\\\') , '%')
                        )
                    "),
                'left'
            )
            ->join(
                $databasesTable,
                $databasesInstance->qualifyColumn('name'),
                '=',
                DB::raw("
                        {$builder->qualifyColumn('db_base')}
                        AND {$databasesInstance->qualifyColumn('database_server_id')} = {$serversInstance->getQualifiedKeyName()}
                        AND {$databasesInstance->qualifyColumn('deleted_at')} IS NULL
                    "),
                'left'
            )
            ->addSelect($databasesInstance->qualifyColumns([
                "{$databasesInstance->getKeyName()} as database_id",
                'company_code',
            ]))
            ->addSelect($serversInstance->qualifyColumns([
                "{$serversInstance->getKeyName()} as database_server_id",
            ]));
    }
}
