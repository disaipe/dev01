<?php

namespace App\Modules\ComputerMonitor;

use App\Core\Enums\JobProtocolState;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\CountExpression;
use App\Core\Report\ExpressionType\QueryExpressionType;
use App\Filament\Components\ConditionBuilder;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Models\JobProtocol;
use App\Modules\ActiveDirectory\ADComputerEntryReference;
use App\Modules\ActiveDirectory\Models\ADComputerEntry;
use App\Modules\ComputerMonitor\Commands\CheckComputerCommand;
use App\Modules\ComputerMonitor\Jobs\ComputersSyncJob;
use App\Modules\ComputerMonitor\Models\ADComputerEntryStatus;
use App\Support\Forms\RpcConnectionSettingsForm;
use Cron\CronExpression;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ComputerMonitorServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'pcmon';

    public function init(): void
    {
        $this->loadMigrations();
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        $this->commands([
            CheckComputerCommand::class,
        ]);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => $this->namespace,
                'code' => 'PCMON_COMPUTER_COUNT_BY_AD_USER',
                'type' => QueryExpressionType::class,
                'name' => '[PCMON] Количество персональных ПК (по активности пользователей)',
                'expression' => new CountExpression('ad_computer_entries.name'),
                'options' => [
                    'model' => ADComputerEntryStatus::class,
                    'query' => function (Builder $query) {
                        $adComputerEntry = new ADComputerEntry();
                        $adComputerTable = $adComputerEntry->getTable();

                        return $query
                            ->join(
                                $adComputerTable,
                                "{$adComputerTable}.id",
                                '=',
                                "{$query->getModel()->getTable()}.ad_computer_entry_id"
                            )
                            ->select("{$adComputerTable}.name")
                            ->groupBy("{$adComputerTable}.name")
                            ->distinct();
                    },
                ],
            ]),
        ]);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('pcmon::messages.name'),
            'description' => __('pcmon::messages.description'),
            'casts' => [
                'filters' => 'json',
            ],
            'view' => [
                'config' => [
                    RawHtmlContent::make(function ($get) {
                        $out = '';

                        $lastSync = JobProtocol::query()
                            ->where('name', '=', ComputersSyncJob::class)
                            ->where('state', '=', JobProtocolState::Ready->value)
                            ->orderByDesc('ended_at')
                            ->first();

                        if ($lastSync) {
                            $out = '<div class="text-right text-sm">'.
                                __('admin.last sync date', ['date' => $lastSync->ended_at]).
                                '</div>';
                        }

                        $syncEnabled = $get('ComputerSync.enabled');
                        $schedule = $get('ComputerSync.schedule');

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
                                ...RpcConnectionSettingsForm::make('', 'computer_sync_job'),

                                Group::make([
                                    Select::make('source')
                                        ->label(__('pcmon::messages.source'))
                                        ->options(function () {
                                            /** @var ReferenceManager $references */
                                            $references = app('references');

                                            return Arr::mapWithKeys(
                                                $references->getReferences(),
                                                fn (ReferenceEntry $ref) => [$ref->getName() => $ref->getPluralLabel()]
                                            );
                                        })
                                        ->reactive()
                                        ->required(),

                                    Select::make('dns_field')
                                        ->label(__('pcmon::messages.dns field'))
                                        ->options(function (\Closure $get) {
                                            $source = $get('source');

                                            if ($source) {
                                                /** @var ReferenceManager $references */
                                                $references = app('references');

                                                $reference = $references->getByName($source);

                                                if ($reference) {
                                                    $schema = $reference->getSchema();

                                                    return Arr::mapWithKeys(
                                                        $schema,
                                                        fn ($value, $key) => [$key => $value->getAttribute('label') ?? $key]
                                                    );
                                                }
                                            }

                                            return [];
                                        })
                                        ->required(),

                                    TextInput::make('chunk_size')
                                        ->label(__('pcmon::messages.chunk size'))
                                        ->helperText(__('pcmon::messages.chunk size help'))
                                        ->numeric()
                                        ->default(200),

                                    Toggle::make('parallel')
                                        ->label(__('pcmon::messages.parallel'))
                                        ->helperText(__('pcmon::messages.parallel help'))
                                        ->inline(false),
                                ])
                                    ->columns(2),

                                ConditionBuilder::make('filters')
                                    ->label(__('pcmon::messages.filter'))
                                    ->helperText(__('pcmon::messages.filter help'))
                                    ->fields(function () {
                                        $schema = (new ADComputerEntryReference())->getSchema();

                                        return Arr::mapWithKeys(
                                            $schema,
                                            fn ($value, $key) => [$key => $value->getAttribute('label') ?? $key]
                                        );
                                    }),
                            ]),

                            Section::make(__('pcmon::messages.job.computers sync.title'))
                                ->schema([
                                    RawHtmlContent::make(__('pcmon::messages.job.computers sync.description')),

                                    CronExpressionInput::make('ComputerSync.schedule')
                                        ->label(__('admin.schedule')),

                                    Checkbox::make('ComputerSync.enabled')
                                        ->label(__('admin.enabled')),

                                    FormButton::make('runAllComputersJob')
                                        ->label(__('admin.run'))
                                        ->action(fn () => $this->runAllComputersJob()),
                                ]),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('pcmon::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new ComputersSyncJob(), 'ComputerSync');
    }

    public function runAllComputersJob(): void
    {
        try {
            ComputersSyncJob::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }
}
