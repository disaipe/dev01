<?php

namespace App\Modules\ActiveDirectory;

use App\Core\Enums\JobProtocolState;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\CountExpression;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Models\Domain;
use App\Models\JobProtocol;
use App\Modules\ActiveDirectory\Commands\SyncComputersCommand;
use App\Modules\ActiveDirectory\Commands\SyncUsersCommand;
use App\Modules\ActiveDirectory\Filament\Components\LdapFilterBuilder;
use App\Modules\ActiveDirectory\Job\ADSyncComputersJob;
use App\Modules\ActiveDirectory\Job\ADSyncUsersJob;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use App\Modules\ActiveDirectory\Utils\LdapQueryConditionsBuilder;
use App\Services\LdapService;
use Cron\CronExpression;
use Error;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\Computer;
use LdapRecord\Models\ActiveDirectory\Entry;
use LdapRecord\Models\ActiveDirectory\User;

class ADServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'ad';

    public function init(): void
    {
        $this->loadMigrations();

        $this->commands([
            SyncUsersCommand::class,
            SyncComputersCommand::class,
        ]);

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(ADUserEntryReference::class);
        $references->register(ADComputerEntryReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => 'AD',
                'code' => 'AD_ENTRY_COUNT',
                'name' => 'Количество учетных записей',
                'model' => ADUserEntry::class,
                'query' => fn ($query) => $query->active(),
                'expression' => new CountExpression(),
            ]),
            Indicator::fromArray([
                'module' => 'AD',
                'code' => 'AD_LYNC_COUNT',
                'name' => 'Количество учетных записей Lync',
                'model' => ADUserEntry::class,
                'query' => fn ($query) => $query->active()->where('sip_enabled', '=', true),
                'expression' => new CountExpression(),
            ]),
        ]);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('ad::messages.name'),
            'description' => __('ad::messages.description'),
            'casts' => [
                'users.filters' => 'json',
                'computers.filters' => 'json',
            ],
            'view' => [
                'config' => [
                    Section::make(__('ad::messages.section_sync'))
                        ->schema([
                            Select::make('domain_id')
                                ->label(trans_choice('admin.domain', 1))
                                ->options(Domain::all()->pluck('name', 'id'))
                                ->required(),
                        ]),

                    Tabs::make('integrations')->tabs([
                        Tabs\Tab::make(__('ad::messages.job.users.title'))
                            ->statePath('users')
                            ->schema($this->getIntegrationOptions('users', ADSyncUsersJob::class)),

                        Tabs\Tab::make(__('ad::messages.job.computers.title'))
                            ->statePath('computers')
                            ->schema($this->getIntegrationOptions('computers', ADSyncComputersJob::class)),
                    ]),
                ],
            ],
        ];
    }

    public function getIntegrationOptions(string $type, string $syncJob): array
    {
        return [
            RawHtmlContent::make(function ($get) use ($syncJob) {
                $out = '';

                $lastSync = JobProtocol::query()
                    ->where('name', '=', $syncJob)
                    ->where('state', '=', JobProtocolState::Ready->value)
                    ->orderByDesc('ended_at')
                    ->first();

                if ($lastSync) {
                    $out = '<div class="text-right text-sm">'.
                        __('admin.last sync date', ['date' => $lastSync->ended_at]).
                        '</div>';
                }

                $syncEnabled = $get('enabled');
                $schedule = $get('schedule');

                if ($syncEnabled && $schedule) {
                    $expr = new CronExpression($schedule);
                    $nextDate = $expr->getNextRunDate();
                    $nextDateStr = $nextDate->format('Y-m-d H:i:s');

                    $out .= '<div class="text-right text-sm">'.
                        __('admin.next sync date', ['date' => $nextDateStr]).
                        '</div>';
                }

                return $out;
            })
                ->columnSpanFull(),

            Checkbox::make('enabled')
                ->label(__('admin.enabled')),

            Textarea::make('base_dn')
                ->label(__('ad::messages.base dn or ou'))
                ->helperText(__('ad::messages.base dn or ou helper')),

            LdapFilterBuilder::make('filters')
                ->label(__('ad::messages.filter')),

            RawHtmlContent::make(__('ad::messages.action.test filters.description')),

            FormButton::make("runFiltersTest-{$type}")
                ->label(__('ad::messages.action.test filters.title'))
                ->action(fn ($state) => $this->runFiltersTest($state, $type)),

            RawHtmlContent::make(__("ad::messages.job.{$type}.description")),

            CronExpressionInput::make('schedule')
                ->disableLabel(),

            FormButton::make("runJob-{$type}")
                ->label(__('admin.run'))
                ->action(fn () => $this->runJob($syncJob)),
        ];
    }

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new ADSyncUsersJob(), 'users');
        $this->scheduleJob($schedule, new ADSyncComputersJob(), 'computers');
    }

    public function runJob(string $jobType): void
    {
        try {
            app($jobType)::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (Exception|Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }

    public function runFiltersTest(array $config, string $type): void
    {
        $domainId = $this->module->getConfig('domain_id');
        $filters = Arr::get($config, 'filters');

        try {
            /** @var Domain $domain */
            $domain = Domain::query()->find($domainId);
            LdapService::addDomainConnection($domain);
            Container::setDefault($domain->code);

            $baseDN = Arr::get($config, 'base_dn', $domain->base_dn);
            $baseOUs = explode("\n", $baseDN);

            $query = match ($type) {
                'users' => User::query(),
                'computers' => Computer::query(),
                default => Entry::query()
            };

            $query->select('dn')->limit(1000);

            if ($filters) {
                LdapQueryConditionsBuilder::applyToQuery($query, $filters);
            }

            $results = [];

            foreach ($baseOUs as $ou) {
                $query->in($ou);

                $results = array_merge($results, $query->get()->toArray());
            }

            $uniques = collect($results)->unique(fn (Entry $entry) => $entry->getDn());
            $count = $uniques->count();

            if ($count) {
                Filament::notify(
                    'success',
                    __('ad::messages.action.test filters.found N records', ['count' => $count])
                );
            } else {
                Filament::notify('warning', __('ad::messages.action.test filters.records not found'));
            }
        } catch (Exception $e) {
            Filament::notify('danger', $e->getMessage());
        }
    }
}
