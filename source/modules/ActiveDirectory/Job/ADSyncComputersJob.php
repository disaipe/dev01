<?php

namespace App\Modules\ActiveDirectory\Job;

use App\Core\Module\ModuleScheduledJob;
use App\Models\Domain;
use App\Modules\ActiveDirectory\Models\ADComputerEntry;
use App\Modules\ActiveDirectory\Utils\LdapQueryConditionsBuilder;
use App\Services\LdapService;
use Carbon\Carbon;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\Computer;
use LdapRecord\Models\Collection;

class ADSyncComputersJob extends ModuleScheduledJob
{
    protected int $loadedCount = 0;

    public function work(): ?array
    {
        $module = $this->getModule();

        $domainId = $module->getConfig('domain_id');
        $filters = $module->getConfig('computers.filters');

        /** @var Domain $domain */
        $domain = Domain::query()->find($domainId);
        LdapService::addDomainConnection($domain);
        Container::setDefault($domain->code);

        $baseDN = $module->getConfig('computers.base_dn') ?? $domain->base_dn;
        $baseOUs = explode("\n", $baseDN);

        $query = Computer::query()
            ->select([
                'name',
                'operatingSystem',
                'operatingSystemVersion',
                'dnsHostName',
                'whenCreated',
                'whenChanged',
            ]);

        if ($filters) {
            LdapQueryConditionsBuilder::applyToQuery($query, $filters);
        }

        // Truncate table to remove records not presented after filter changes
        ADComputerEntry::withoutTimestamps(function () {
            ADComputerEntry::query()->delete();
        });

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
        return __('ad::messages.job.computers.title');
    }

    protected function processChunk(Collection $entries): int
    {
        $now = Carbon::now();

        $records = [];

        foreach ($entries as $computer) {
            /** @var Computer $computer */
            $name = $computer->getFirstAttribute('name');

            $record = [
                'name' => $name,
                'dns_name' => $computer->getFirstAttribute('dnsHostName'),
                'ou_path' => $computer->getParentDn(),
                'operating_system' => $computer->getFirstAttribute('operatingSystem'),
                'operating_system_version' => $computer->getFirstAttribute('operatingSystemVersion'),
                'created_at' => $computer->getFirstAttribute('whenCreated'),
                'updated_at' => $computer->getFirstAttribute('whenChanged'),
                'synced_at' => $now,
                'deleted_at' => null,
            ];

            $records []= $record;
        }

        $fillable = (new ADComputerEntry())->getFillable();

        ADComputerEntry::withoutEvents(fn () =>
            ADComputerEntry::withoutTimestamps(fn () =>
                ADComputerEntry::withTrashed()->upsert($records, 'name', $fillable)
            )
        );

        return $entries->count();
    }
}
