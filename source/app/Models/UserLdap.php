<?php

namespace App\Models;

use Illuminate\Http\Request;

class UserLdap extends \LdapRecord\Models\ActiveDirectory\User
{
    public function __construct(array $attributes = [])
    {
        /** @var Request $request */
        $request = app('request');
        $domainKey = $request->input('domain');

        if ($domainKey) {
            /** @var Domain $domain */
            $domain = Domain::query()->find($domainKey);

            if ($domain && $domain->enabled) {
                $this->connection = $domain->code;
            }
        }

        parent::__construct($attributes);
    }
}
