<?php

namespace App\Modules\FileStorageMonitor;

use App\Core\Enums\JobProtocolState;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\SumExpression;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Models\JobProtocol;
use App\Modules\FileStorageMonitor\Commands\FileStorageSizeCommand;
use App\Modules\FileStorageMonitor\Jobs\FileStoragesSyncJob;
use App\Modules\FileStorageMonitor\Models\FileStorage;
use Cron\CronExpression;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FileStorageMonitorServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'fsmonitor';

    public function init(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/view', $this->namespace);

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(FileStorageReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => 'FSMONITOR',
                'code' => 'FSMONITOR_STORAGE_SIZE_SUM',
                'name' => 'Размер файлового хранилища',
                'model' => FileStorage::class,
                'query' => fn ($query) => $query->reportable(),
                'expression' => new SumExpression('size'),
                'mutator' => fn ($value) => round($value / 1000 / 1000 / 1000, 2),
            ]),
        ]);

        $this->commands([
            FileStorageSizeCommand::class,
        ]);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('fsmonitor::messages.name'),
            'description' => __('fsmonitor::messages.description'),
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

                        $syncEnabled = $get('FileStorageSync.enabled');
                        $schedule = $get('FileStorageSync.schedule');

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
                                    ->label(__('fsmonitor::messages.base url'))
                                    ->helperText(__('fsmonitor::messages.base url help'))
                                    ->required(),

                                TextInput::make('secret')
                                    ->label(__('fsmonitor::messages.secret'))
                                    ->helperText(__('fsmonitor::messages.secret help')),

                                FormButton::make('testConnection')
                                    ->label(__('fsmonitor::messages.action.test service.title'))
                                    ->action(fn ($state) => $this->testConnection($state)),
                            ]),

                            Section::make(__('fsmonitor::messages.job.storages sync.title'))
                                ->schema([
                                    RawHtmlContent::make(__('fsmonitor::messages.job.storages sync.description')),

                                    CronExpressionInput::make('FileStorageSync.schedule')
                                        ->label(__('admin.schedule')),

                                    Checkbox::make('FileStorageSync.enabled')
                                        ->label(__('admin.enabled')),

                                    FormButton::make('runAllStoragesJob')
                                        ->label(__('admin.run'))
                                        ->action(fn () => $this->runAllStoragesJob()),
                                ]),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('fsmonitor::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule)
    {
        $this->scheduleJob($schedule, new FileStoragesSyncJob(), 'FileStorageSync');
    }

    public function testConnection($state)
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
                $notifyMessage = __('fsmonitor::messages.action.test service.success');
            } elseif ($resp->unauthorized()) {
                $notifyMessage = __('fsmonitor::messages.action.test service.wrong secret');
            } else {
                $notifyMessage = __('fsmonitor::messages.action.test service.request failed').$resp->reason();
            }
        } catch (\Exception $e) {
            $notifyMessage = __('admin.error').': '.$e->getMessage();
        }

        Filament::notify($notifyType, $notifyMessage);
    }

    public function runAllStoragesJob()
    {
        try {
            FileStoragesSyncJob::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }
}
