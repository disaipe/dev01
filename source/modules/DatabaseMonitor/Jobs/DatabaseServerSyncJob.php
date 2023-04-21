<?php

namespace App\Modules\DatabaseMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\DatabaseMonitor\Enums\DatabaseServerStatus;
use App\Modules\DatabaseMonitor\Models\DatabaseServer;
use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DatabaseServerSyncJob extends ModuleScheduledJob
{
    private string $columnId = 'dbid';

    private string $columnName = 'name';

    private string $columnSize = 'size';

    private string $columnCompany = 'company';

    protected int $serverId;

    private ?DatabaseServer $server;

    public function __construct($serverId)
    {
        parent::__construct();

        $this->serverId = $serverId;

        /** @var DatabaseServer $server */
        $this->server = DatabaseServer::query()->find($this->serverId);
    }

    /**
     * @throws \Exception
     */
    public function work(): ?array
    {
        if (! $this->server) {
            throw new \Exception(__(
                'dbmon::messages.job.databases sync.errors.server not found',
                ['id' => $this->serverId]
            ));
        }

        return $this->processServer($this->server);
    }

    public function getDescription(): ?string
    {
        return __('dbmon::messages.job.database sync.title', ['server' => $this->server->name]);
    }

    protected function processServer(DatabaseServer $server): array
    {
        $connectionParams = [
            'driver' => $server->type,
            'user' => $server->username,
            'password' => $server->password,
            'host' => $server->host,
            'port' => $server->port,
            'driverOptions' => $server->getOptions(),
        ];

        $server->last_check = Carbon::now();

        try {
            /** @var Connection $conn */
            $conn = DriverManager::getConnection($connectionParams);
            $conn->connect();
        } catch (\Exception $e) {
            return $this->handleException($server, $e);
        }

        try {
            $databases = $this->getDatabases($conn, Arr::get($connectionParams, 'driver'));
            $this->storeDatabases($server, $databases);
        } catch (\Exception $e) {
            return $this->handleException($server, $e);
        }

        $server->last_status = DatabaseServerStatus::Online;
        $server->last_error = null;
        $server->saveQuietly();

        return $databases;
    }

    /**
     * @throws Exception
     */
    protected function getDatabases(Connection $conn, string $driver): array
    {
        return match ($driver) {
            'sqlsrv', 'pdo_sqlsrv' => $this->getSqlServerDatabases($conn),
            'mysql', 'pdo_mysql' => $this->getMysqlServerDatabases($conn),
            default => [],
        };
    }

    /**
     * @throws Exception
     */
    protected function getSqlServerDatabases(Connection $conn): array
    {
        $result = $conn->executeQuery("
             select
                d.dbid as '{$this->columnId}',
                d.name as '{$this->columnName}',
                sum(f.size) * 8 as '{$this->columnSize}'
            from master.dbo.sysdatabases as d
            left join sys.master_files as f on f.database_id = d.dbid
            group by d.dbid, d.name
        ");

        $databases = $result->fetchAllAssociative();

        $propertyName = config('module.databaseMonitor.sqlserver.organization_prop');
        if ($propertyName) {
            foreach ($databases as $database) {
                $name = Arr::get($database, $this->columnName);
                $r = $conn->executeQuery("
                    select value
                    from [{$name}].sys.extended_properties
                    where
                        name = '{$propertyName}'
                        and class_desc = 'DATABASE'
                ");

                $company = $r->fetchFirstColumn();
                Arr::set($database, $this->columnCompany, $company);
            }
        }

        return $databases;
    }

    /**
     * @throws Exception
     */
    protected function getMysqlServerDatabases(Connection $conn): array
    {
        $result = $conn->executeQuery("
            select
                table_schema as '{$this->columnId}',
                table_schema as '{$this->columnName}',
                sum(data_length + index_length) as '{$this->columnSize}'
            from information_schema.tables
            group by table_schema
        ");

        return $result->fetchAllAssociative();
    }

    protected function storeDatabases(DatabaseServer $server, array $databases)
    {
        $dbids = Arr::pluck($databases, $this->columnId);
        $server->databases()->whereNotIn('dbid', $dbids)->forceDelete();

        foreach ($databases as $database) {
            $dbid = Arr::get($database, $this->columnId);
            $name = Arr::get($database, $this->columnName);
            $size = Arr::get($database, $this->columnSize);
            $company = Arr::get($database, $this->columnCompany);

            if (! $dbid) {
                continue;
            }

            $server->databases()->updateOrCreate([
                'dbid' => $dbid,
            ], [
                'name' => $name,
                'size' => $size,
                'company_code' => $company,
            ]);
        }
    }

    private function handleException(DatabaseServer $server, \Exception $exception): array
    {
        $server->last_status = DatabaseServerStatus::Unknown;
        $server->last_error = $exception->getMessage();
        $server->saveQuietly();

        Log::error($exception);

        return [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ];
    }
}
