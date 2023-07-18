<?php

namespace App\Modules\MSExchangeMonitor;

use App\Core\Enums\JobProtocolState;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Report\Expression\CountExpression;
use App\Core\Report\Expression\SumExpression;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Models\JobProtocol;
use App\Modules\FileStorageMonitor\Jobs\FileStoragesSyncJob;
use App\Modules\MSExchangeMonitor\Commands\MSExchangeStatsCommand;
use App\Modules\MSExchangeMonitor\Jobs\MSExchangeStatsSyncJob;
use App\Modules\MSExchangeMonitor\Models\MSExchangeMailboxStat;
use App\Utils\Size;
use Cron\CronExpression;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MSExchangeMonitorServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'msexmonitor';

    public function init(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/view', $this->namespace);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => 'MSEXMONITOR',
                'code' => 'MSEXMONITOR_MAILBOX_SIZE_SUM',
                'name' => __('msexmonitor::messages.indicators.MSEXMONITOR_MAILBOX_SIZE_SUM'),
                'model' => MSExchangeMailboxStat::class,
                'expression' => new SumExpression('total_item_size'),
                'mutator' => fn ($value) => Size::ToGigabytes($value),
            ]),
            Indicator::fromArray([
                'module' => 'MSEXMONITOR',
                'code' => 'MSEXMONITOR_MAILBOX_COUNT',
                'name' => __('msexmonitor::messages.indicators.MSEXMONITOR_MAILBOX_COUNT'),
                'model' => MSExchangeMailboxStat::class,
                'expression' => new CountExpression(),
            ]),
            Indicator::fromArray([
                'module' => 'MSEXMONITOR',
                'code' => 'MSEXMONITOR_MAILBOX_OVERSIZE_COUNT',
                'name' => __('msexmonitor::messages.indicators.MSEXMONITOR_MAILBOX_OVERSIZE_COUNT'),
                'model' => MSExchangeMailboxStat::class,
                'query' => fn ($query) => $query->where('total_item_size', '>', Size::Gigabyte(3)),
                'expression' => new CountExpression(),
            ]),
            Indicator::fromArray([
                'module' => 'MSEXMONITOR',
                'code' => 'MSEXMONITOR_MAILBOX_OVERSIZE_SUM',
                'name' => __('msexmonitor::messages.indicators.MSEXMONITOR_MAILBOX_OVERSIZE_SUM'),
                'model' => MSExchangeMailboxStat::class,
                'expression' => new OversizeSumExpression(),
            ]),
        ]);

        $this->commands([
            MSExchangeStatsCommand::class,
        ]);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('msexmonitor::messages.name'),
            'description' => __('msexmonitor::messages.description'),
            'view' => [
                'config' => [
                    RawHtmlContent::make(function ($get) {
                        $out = '';

                        $lastSync = JobProtocol::query()
                            ->where('name', '=', FileStoragesSyncJob::class)
                            ->where('state', '=', JobProtocolState::Ready->value)
                            ->orderByDesc('ended_at')
                            ->first();

                        if ($lastSync) {
                            $out = '<div class="text-right text-sm">'.
                                __('admin.last sync date', ['date' => $lastSync->ended_at]).
                                '</div>';
                        }

                        $syncEnabled = $get('MSExchangeStatsSync.enabled');
                        $schedule = $get('MSExchangeStatsSync.schedule');

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

                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.configuration'))->schema([
                            Section::make(__('admin.common'))->schema([
                                TextInput::make('base_url')
                                    ->label(__('msexmonitor::messages.base url'))
                                    ->helperText(__('msexmonitor::messages.base url help'))
                                    ->required(),

                                TextInput::make('secret')
                                    ->label(__('msexmonitor::messages.secret'))
                                    ->helperText(__('msexmonitor::messages.secret help')),

                                FormButton::make('testConnection')
                                    ->label(__('msexmonitor::messages.action.test service.title'))
                                    ->action(fn ($state) => $this->testConnection($state)),

                                Grid::make()->schema([
                                    TextInput::make('default_limit')
                                        ->label(__('msexmonitor::messages.default limit'))
                                        ->helperText(__('msexmonitor::messages.default limit help'))
                                        ->numeric(),

                                    TextInput::make('oversize_step')
                                        ->label(__('msexmonitor::messages.oversize step'))
                                        ->helperText(__('msexmonitor::messages.oversize step help'))
                                        ->numeric(),
                                ]),
                            ]),

                            Section::make(__('msexmonitor::messages.job.mailbox size sync.title'))
                                ->schema([
                                    RawHtmlContent::make(__('msexmonitor::messages.job.mailbox size sync.description')),

                                    CronExpressionInput::make('MSExchangeStatsSync.schedule')
                                        ->label(__('admin.schedule')),

                                    Checkbox::make('MSExchangeStatsSync.enabled')
                                        ->label(__('admin.enabled')),

                                    FormButton::make('runJob')
                                        ->label(__('admin.run'))
                                        ->action(fn () => $this->runJob()),
                                ]),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('msexmonitor::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new MSExchangeStatsSyncJob(), 'MSExchangeStatsSync');
    }

    public function testConnection($state): void
    {
        $appUrl = Arr::get($state, 'base_url');
        $secret = Arr::get($state, 'secret');

        $notifyType = 'danger';

        try {
            $resp = Http::withHeaders(['X-SECRET' => $secret])
                ->asJson()
                ->post("$appUrl/get");

            if ($resp->status() == 400) {
                $notifyType = 'success';
                $notifyMessage = __('msexmonitor::messages.action.test service.success');
            } elseif ($resp->unauthorized()) {
                $notifyMessage = __('msexmonitor::messages.action.test service.wrong secret');
            } else {
                $notifyMessage = __('msexmonitor::messages.action.test service.request failed').$resp->reason();
            }
        } catch (\Exception $e) {
            $notifyMessage = __('admin.error').': '.$e->getMessage();
        }

        Filament::notify($notifyType, $notifyMessage);
    }

    public function runJob(): void
    {
        try {
            MSExchangeStatsSyncJob::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }
}
