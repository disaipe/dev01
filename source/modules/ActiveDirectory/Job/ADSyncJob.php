<?php

namespace App\Modules\ActiveDirectory\Job;

use App\Core\Module\ModuleScheduledJob;
use App\Models\Domain;
use App\Modules\ActiveDirectory\Models\ADEntry;
use App\Modules\ActiveDirectory\Utils\LdapQueryConditionsBuilder;
use App\Services\LdapService;
use Illuminate\Support\Arr;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\Collection;

class ADSyncJob extends ModuleScheduledJob
{
    protected int $loadedCount = 0;

    public function work(): ?array
    {
        $config = $this->getModuleConfig();

        $domainId = Arr::get($config, 'domain_id');
        $filters = Arr::get($config, 'filters');

        /** @var Domain $domain */
        $domain = Domain::query()->find($domainId);
        LdapService::addDomainConnection($domain);
        Container::setDefault($domain->code);

        $baseDN = Arr::get($config, 'base_dn', $domain->base_dn);
        $baseOUs = explode("\n", $baseDN);

        $query = User::query()
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
                'msExchMailboxGuid',    // MS exchange GUID
            ]);

        if ($filters) {
            LdapQueryConditionsBuilder::applyToQuery($query, $filters);
        }

        foreach ($baseOUs as $ou) {
            $query->in($ou);

            $query->chunk(200, function (Collection $entries) {
                $this->loadedCount += $this->processChunk($entries);
            });
        }

        return [
            'result' => $this->loadedCount,
        ];
    }

    public function getDescription(): ?string
    {
        return __('ad::messages.job.ldap sync.title');
    }

    protected function processChunk(Collection $entries): int
    {
        foreach ($entries as $user) {
            /** @var User $user */
            $username = $user->getFirstAttribute('sAMAccountName');
            $state = intval($user->getFirstAttribute('userAccountControl'));
            $blocked = $user->isDisabled();
            $lastLogonDate = $user->getAttribute('lastlogon') ?: null;
            $msExchMailboxGuid = $user->getFirstAttribute('msExchMailboxGuid');

            $record = [
                'company_prefix' => $user->getFirstAttribute('employeeType'),
                'company_name' => $user->getFirstAttribute('company'),
                'username' => $username,
                'name' => $user->getFirstAttribute('name'),
                'department' => $user->getFirstAttribute('department'),
                'post' => $user->getFirstAttribute('title'),
                'email' => $user->getFirstAttribute('mail'),
                'ou_path' => $user->getParentDn(),
                'groups' => $user->getAttribute('memberOf'),
                'last_logon' => $lastLogonDate,
                'logon_count' => intval($user->getFirstAttribute('logonCount')),
                'state' => $state,
                'sip_enabled' => $user->getFirstAttribute('msRTCSIP-UserEnabled') === 'TRUE',
                'mailbox_guid' => $msExchMailboxGuid,
                'blocked' => $blocked,
            ];

            ADEntry::query()->updateOrCreate([
                'username' => $username,
            ], $record);
        }

        return $entries->count();
    }
}
