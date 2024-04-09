<?php

namespace App\Modules\ActiveDirectory\Job;

use App\Core\Module\ModuleScheduledJob;
use App\Models\Domain;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use App\Modules\ActiveDirectory\Utils\LdapQueryConditionsBuilder;
use App\Services\LdapService;
use App\Utils\DomainUtils;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\Attributes\Guid;
use LdapRecord\Models\Collection;

class ADSyncUsersJob extends ModuleScheduledJob
{
    protected int $loadedCount = 0;

    public function work(): ?array
    {
        $module = $this->getModule();

        $domainId = $module->getConfig('domain_id');
        $filters = $module->getConfig('users.filters');

        /** @var Domain $domain */
        $domain = Domain::query()->find($domainId);
        LdapService::addDomainConnection($domain);
        Container::getInstance()->setDefaultConnection($domain->code);

        $baseDN = $module->getConfig('users.base_dn') ?? $domain->base_dn;
        $baseOUs = DomainUtils::parseOUs($baseDN);

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

        // Truncate table to remove records not presented after filter changes
        ADUserEntry::query()->truncate();

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
        return __('ad::messages.job.users.title');
    }

    protected function processChunk(Collection $entries): int
    {

        $records = [];

        foreach ($entries as $user) {
            /** @var User $user */
            $username = $user->getFirstAttribute('sAMAccountName');
            $state = intval($user->getFirstAttribute('userAccountControl'));
            $blocked = $user->isDisabled();
            $lastLogonDate = $user->getAttribute('lastlogon') ?: null;
            $msExchMailboxGuid = $user->getFirstAttribute('msExchMailboxGuid');

            if ($msExchMailboxGuid) {
                $guid = new Guid($msExchMailboxGuid);
                $msExchMailboxGuid = $guid->getValue();
            }

            $record = [
                'company_prefix' => $user->getFirstAttribute('employeeType'),
                'company_name' => $user->getFirstAttribute('company'),
                'username' => $username,
                'name' => $user->getFirstAttribute('name'),
                'department' => $user->getFirstAttribute('department'),
                'post' => $user->getFirstAttribute('title'),
                'email' => $user->getFirstAttribute('mail'),
                'ou_path' => $user->getParentDn(),
                'groups' => json_encode($user->getAttribute('memberOf'), JSON_UNESCAPED_UNICODE),
                'last_logon' => $lastLogonDate,
                'logon_count' => intval($user->getFirstAttribute('logonCount')),
                'state' => $state,
                'sip_enabled' => $user->getFirstAttribute('msRTCSIP-UserEnabled') === 'TRUE',
                'mailbox_guid' => $msExchMailboxGuid,
                'blocked' => $blocked,
            ];

            $records[] = $record;
        }

        ADUserEntry::withoutEvents(function () use ($records) {
            ADUserEntry::query()->upsert($records, 'username');
        });

        return $entries->count();
    }
}
