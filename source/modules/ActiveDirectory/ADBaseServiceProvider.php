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
use App\Modules\ActiveDirectory\Commands\LdapSync;
use App\Modules\ActiveDirectory\Filament\Components\LdapFilterBuilder;
use App\Modules\ActiveDirectory\Job\ADSyncJob;
use App\Modules\ActiveDirectory\Models\ADEntry;
use App\Modules\ActiveDirectory\Utils\LdapQueryConditionsBuilder;
use App\Services\LdapService;
use Cron\CronExpression;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User;

class ADBaseServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'ad';

    public function init(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->commands([
            LdapSync::class,
        ]);

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(ADEntryReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => 'AD',
                'code' => 'AD_ENTRY_COUNT',
                'name' => 'Количество учетных записей',
                'model' => ADEntry::class,
                'expression' => new CountExpression(),
            ]),
            Indicator::fromArray([
                'module' => 'AD',
                'code' => 'AD_LYNC_COUNT',
                'name' => 'Количество учетных записей Lync',
                'model' => ADEntry::class,
                'query' => fn ($query) => $query->where('sip_enabled', '=', true),
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
                'filters' => 'json',
            ],
            'view' => [
                'config' => [
                    RawHtmlContent::make(function ($get) {
                        $out = '';

                        $lastSync = JobProtocol::query()
                            ->where('name', '=', ADSyncJob::class)
                            ->where('state', '=', JobProtocolState::Ready->value)
                            ->orderByDesc('ended_at')
                            ->first();

                        if ($lastSync) {
                            $out = '<div class="text-right text-sm">'.
                                __('admin.last sync date', ['date' => $lastSync->ended_at]).
                                '</div>';
                        }

                        $syncEnabled = $get('LdapSync.enabled');
                        $schedule = $get('LdapSync.schedule');

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

                    Section::make(__('ad::messages.section_sync'))
                        ->schema([
                            Select::make('domain_id')
                                ->label(trans_choice('admin.domain', 1))
                                ->options(Domain::all()->pluck('name', 'id'))
                                ->required(),

                            Textarea::make('base_dn')
                                ->label(__('ad::messages.base dn or ou'))
                                ->helperText(__('ad::messages.base dn or ou helper')),

                            LdapFilterBuilder::make('filters')
                                ->label(__('ad::messages.filter')),

                            RawHtmlContent::make(__('ad::messages.action.test filters.description')),

                            FormButton::make('runFiltersTest')
                                ->label(__('ad::messages.action.test filters.title'))
                                ->action(fn ($state) => $this->runFiltersTest($state)),
                        ]),

                    Section::make(__('ad::messages.job.ldap sync.title'))
                        ->schema([
                            RawHtmlContent::make(__('ad::messages.job.ldap sync.description')),

                            CronExpressionInput::make('LdapSync.schedule')
                                ->disableLabel(),

                            Checkbox::make('LdapSync.enabled')
                                ->label(__('admin.enabled')),

                            FormButton::make('runJob')
                                ->label(__('admin.run'))
                                ->action(fn () => $this->runJob()),
                        ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule)
    {
        $this->scheduleJob($schedule, new ADSyncJob(), 'LdapSync');
    }

    public function runJob()
    {
        try {
            ADSyncJob::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }

    public function runFiltersTest($config)
    {
        $domainId = Arr::get($config, 'domain_id');
        $filters = Arr::get($config, 'filters');

        try {
            /** @var Domain $domain */
            $domain = Domain::query()->find($domainId);
            LdapService::addDomainConnection($domain);
            Container::setDefault($domain->code);

            $baseDN = Arr::get($config, 'base_dn', $domain->base_dn);
            $baseOUs = explode("\n", $baseDN);

            $query = User::query()->select('dn')->limit(1000);

            if ($filters) {
                LdapQueryConditionsBuilder::applyToQuery($query, $filters);
            }

            $results = [];

            foreach ($baseOUs as $ou) {
                $query->in($ou);

                $results = array_merge($results, $query->get()->toArray());
            }

            $uniques = collect($results)->unique(fn (User $entry) => $entry->getDn());
            $count = $uniques->count();

            if ($count) {
                Filament::notify(
                    'success',
                    __('ad::messages.action.test filters.found N records', ['count' => $count])
                );
            } else {
                Filament::notify('warning', __('ad::messages.action.test filters.records not found'));
            }
        } catch (\Exception $e) {
            Filament::notify('danger', $e->getMessage());
        }
    }
}
