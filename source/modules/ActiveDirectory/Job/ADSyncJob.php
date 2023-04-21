<?php

namespace App\Modules\ActiveDirectory\Job;

use App\Core\Module\ModuleScheduledJob;
use App\Models\Domain;
use App\Modules\ActiveDirectory\Models\ADEntry;
use App\Services\LdapService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use LdapRecord\Container;
use LdapRecord\Models\Collection;
use LdapRecord\Models\Entry;

class ADSyncJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $config = $this->getModuleConfig();

        $domainId = Arr::get($config, 'domain_id');
        $filters = Arr::get($config, 'filters');

        /** @var Domain $domain */
        $domain = Domain::query()->find($domainId);
        LdapService::addDomainConnection($domain);
        Container::setDefault($domain->code);

        $base_dn = Arr::get($config, 'base_dn', $domain->base_dn);

        $query = Entry::query()
            ->select([
                'sAMAccountName',       // username
                'name',                 // name
                'employeeType',         // company prefix
                'department',           // department name
                'company',              // company name
                'title',                // post
                'mail',                 // email
                'userAccountControl',   // state
                'memberOf',             // groups
                'msRTCSIP-UserEnabled', // sip enabled flag
                'logonCount',           // logon count
                'lastLogon',            // last logon timestamp
            ])
            ->setBaseDn($base_dn)
            ->where('objectClass', '=', 'user')
            ->where('objectCategory', '=', 'person');

        if ($filters) {
            $query->rawFilter(explode("\n", $filters));
        }

        $query->chunk(200, function (Collection $entries) {
            $this->processChunk($entries);
        });

        return [
            'result' => 'Nothing to return',
        ];
    }

    public function getDescription(): ?string
    {
        return __('ad::messages.job.ldap_sync.title');
    }

    protected function processChunk(Collection $entries)
    {
        foreach ($entries as $entry) {
            /** @var Entry $entry */
            $username = $entry->getFirstAttribute('sAMAccountName');

            // parse state
            $state = intval($entry->getFirstAttribute('userAccountControl'));
            $blocked = ($state & 2) != 0;

            // parse last logon
            // https://teaseo.ru/win/perevodim-active-directory-lastlogon-v-unix-timestamp/1840
            $lastLogon = intval($entry->getFirstAttribute('lastLogon'));
            $lastLogonTimestamp = ($lastLogon / 10000000) - 11644473600;
            $lastLogonDate = Carbon::createFromTimestamp($lastLogonTimestamp, '+3');

            $record = [
                'company_prefix' => $entry->getFirstAttribute('employeeType'),
                'company_name' => $entry->getFirstAttribute('company'),
                'username' => $username,
                'name' => $entry->getFirstAttribute('name'),
                'department' => $entry->getFirstAttribute('department'),
                'post' => $entry->getFirstAttribute('title'),
                'email' => $entry->getFirstAttribute('mail'),
                'ou_path' => $entry->getParentDn(),
                'groups' => $entry->getAttribute('memberOf'),
                'last_logon' => $lastLogonDate->year > 2000 ? $lastLogonDate->toDateTimeString() : null,
                'logon_count' => intval($entry->getFirstAttribute('logonCount')),
                'state' => $state,
                'sip_enabled' => $entry->getFirstAttribute('msRTCSIP-UserEnabled') === 'TRUE',
                'blocked' => $blocked,
            ];

            ADEntry::query()->updateOrCreate([
                'username' => $username,
            ], $record);
        }
    }
}
