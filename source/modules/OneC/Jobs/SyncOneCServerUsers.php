<?php

namespace App\Modules\OneC\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\DatabaseMonitor\Models\DatabaseServer;
use App\Modules\OneC\Enums\DatabaseType;
use App\Modules\OneC\Models\OneCInfoBaseUser;
use App\Modules\OneC\Models\OneCInfoBase;
use App\Utils\DomainUtils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncOneCServerUsers extends ModuleScheduledJob
{
    const USERS_TABLE = 'v8users';

    protected string $server;

    protected ?DatabaseServer $databaseServer;

    protected Collection $infoBases;

    public function __construct(string $server)
    {
        parent::__construct();

        $this->server = $server;
    }

    public function work(): ?array
    {
        if (! $databaseServer = $this->getDatabaseServer()) {
            return [
                'error' => 'No database configuration found',
            ];
        }

        $this->databaseServer = $databaseServer;

        $this->infoBases = $this->getInfoBases();

        if (! $this->infoBases->count()) {
            return [
                'warning' => 'No infobases on given database server found',
            ];
        }

        return $this->getUsers();
    }

    public function getDescription(): ?string
    {
        return __('onec::messages.job.server users sync.description');
    }

    protected function getDatabaseServer(): ?DatabaseServer
    {
        /** @var ?DatabaseServer $result */
        $result = DatabaseServer::query()
            ->where(function (Builder $query) {
                $v = Str::replace('\\', '\\\\', $this->server);

                $query
                    ->where('host', '=', $this->server)
                    ->orWhere('aliases', 'like', "%{$v}%");
            })
            ->first();

        return $result;
    }

    protected function getInfoBases(): Collection
    {
        return OneCInfoBase::query()
            ->where('db_server', '=', $this->server)
            ->where('db_type', '=', DatabaseType::MSSQL->value)
            ->whereNotNull('db_base')
            ->get();
    }

    protected function getUsers(): array
    {
        $connection = $this->getConnection();
        $connection->connect();

        $results = [];

        foreach ($this->infoBases as $infoBase) {
            /** @var OneCInfoBase $infoBase */

            try {
                $userCount = $this->getInfoBaseUsers($infoBase, $connection);
                $results[$infoBase->ref] = [
                    'status' => 'Success',
                    'count' => $userCount,
                ];
            } catch (Exception $e) {
                $results[$infoBase->ref] = [
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    protected function getInfoBaseUsers(OneCInfoBase $infoBase, Connection $connection): int
    {
        $table = self::USERS_TABLE;
        $users = $connection
            ->executeQuery("select Name, OSName from [{$infoBase->db_base}].[dbo].[{$table}]")
            ->fetchAllAssociative();

        if (! $users) {
            return 0;
        }

        $fillable = null;

        DB::transaction(function () use ($users, $infoBase, $fillable) {
            $records = [];

            OneCInfoBaseUser::withoutTimestamps(function () use ($infoBase) {
                OneCInfoBaseUser::withoutEvents(function () use ($infoBase) {
                    OneCInfoBaseUser::query()
                        ->where('one_c_info_base_id', '=', $infoBase->getKey())
                        ->delete();
                });
            });

            foreach ($users as $user) {
                [$domain, $username] = DomainUtils::parseUserName(Arr::get($user, 'OSName'));

                if (! $user && ! $username) {
                    continue;
                }

                $record = [
                    'one_c_info_base_id' => $infoBase->getKey(),
                    'username' => Arr::get($user, 'Name'),
                    'login' => $username,
                    'domain' => $domain,
                    'deleted_at' => null,
                ];

                $records[] = $record;
            }

            OneCInfoBaseUser::withoutEvents(function () use ($records, $fillable) {
                OneCInfoBaseUser::withTrashed()->upsert(
                    $records,
                    ['one_c_info_base_id', 'username'],
                    $fillable
                );
            });
        });

        return count($users);
    }

    protected function getConnection(): Connection
    {
        $connectionConfig = [
            'driver' => $this->databaseServer->type,
            'host' => $this->databaseServer->host,
            'user' => $this->databaseServer->username,
            'password' => $this->databaseServer->password,
            'driverOptions' => $this->databaseServer->getOptions(),
        ];

        return DriverManager::getConnection($connectionConfig);
    }
}
