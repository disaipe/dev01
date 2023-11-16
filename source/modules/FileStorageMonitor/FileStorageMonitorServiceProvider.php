<?php

namespace App\Modules\FileStorageMonitor;

use App\Core\Enums\JobProtocolState;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\SumExpression;
use App\Core\Report\ExpressionType\QueryExpressionType;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Models\JobProtocol;
use App\Modules\FileStorageMonitor\Commands\FileStorageSizeCommand;
use App\Modules\FileStorageMonitor\Jobs\FileStoragesSyncJob;
use App\Modules\FileStorageMonitor\Models\FileStorage;
use App\Modules\FileStorageMonitor\References\FileStorageReference;
use App\Support\Forms\RpcConnectionSettingsForm;
use Cron\CronExpression;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

class FileStorageMonitorServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'fsmonitor';

    public function init(): void
    {
        $this->loadMigrations();
        $this->loadViews();
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(FileStorageReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => 'FSMONITOR',
                'code' => 'FSMONITOR_STORAGE_SIZE_SUM',
                'type' => QueryExpressionType::class,
                'name' => 'Размер файлового хранилища',
                'expression' => new SumExpression(['column' => 'size']),
                'options' => [
                    'model' => FileStorage::class,
                    'query' => fn ($query) => $query->reportable(),
                ],
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
                            Section::make(__('admin.common'))->schema(
                                RpcConnectionSettingsForm::make('', 'get')
                            ),

                            Section::make(__('fsmonitor::messages.job.storages sync.title'))
                                ->schema([
                                    RawHtmlContent::make(__('fsmonitor::messages.job.storages sync.description')),

                                    CronExpressionInput::make('FileStorageSync.schedule')
                                        ->label(__('admin.schedule')),

                                    Toggle::make('FileStorageSync.enabled')
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

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new FileStoragesSyncJob(), 'FileStorageSync');
    }

    public function runAllStoragesJob(): void
    {
        try {
            FileStoragesSyncJob::dispatch();
            Notification::make()->success()->title(__('admin.job started'))->send();
        } catch (\Exception|\Error $e) {
            Notification::make()->danger()->title(__('admin.job staring error'))->body($e->getMessage())->send();
            Log::error($e);
        }
    }
}
