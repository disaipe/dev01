<?php

namespace App\Database\PDO;

use App\Database\PDO\Concerns\ConnectsToDatabase;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;

class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'pdo_mysql';
    }
}
