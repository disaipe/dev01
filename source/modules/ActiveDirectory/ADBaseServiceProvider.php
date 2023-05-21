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
use App\Modules\ActiveDirectory\Job\ADSyncJob;
use App\Modules\ActiveDirectory\Models\ADEntry;
use Cron\CronExpression;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

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

                            TextInput::make('base dn')
                                ->label(__('ad::messages.base dn'))
                                ->helperText(__('ad::messages.base dn helper')),

                            Textarea::make('filters')
                                ->label(__('ad::messages.filter')),
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
            Filament::notify('success', 'Задание запущено');
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', 'Ошибка запуска задания', $e->getMessage());
            Log::error($e);
        }
    }
}
