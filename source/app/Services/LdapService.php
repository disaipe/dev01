<?php

namespace App\Services;

use App\Models\Domain;
use LdapRecord\Connection;
use LdapRecord\Container;

class LdapService
{
    public static function addDomainConnection(Domain $domain): void
    {
        Container::addConnection(
            self::getDomainConnection($domain),
            $domain->code
        );
    }

    public static function getDomainConnection(Domain $domain): Connection
    {
        return new Connection([
            'hosts' => [$domain->host],
            'port' => $domain->port,
            'username' => $domain->username,
            'password' => $domain->password,
            'base_dn' => $domain->base_dn,
            'timeout' => $domain->timeout ?? 5,
            'use_ssl' => $domain->ssl,
            'use_tls' => $domain->tls,
        ]);
    }
}
