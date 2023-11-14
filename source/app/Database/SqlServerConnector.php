<?php

namespace App\Database;

use Illuminate\Database\Connectors\SqlServerConnector as BaseSqlServerConnector;
use PDO;

class SqlServerConnector extends BaseSqlServerConnector
{
    /**
     * Override the default options array to prevent the SQLSRV error:
     * SQLSTATE[IMSSP]: An invalid attribute was designated on the PDO object.
     *
     * @link https://github.com/laravel/framework/issues/47937
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
    ];
}
