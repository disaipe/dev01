<?php

namespace App\Modules\OneC;

use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Module\ModuleScheduledJob;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\CountExpression;
use App\Core\Report\ExpressionType\QueryExpressionType;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Modules\OneC\Commands\OneCSyncDatabaseUsersCommand;
use App\Modules\OneC\Commands\OneCSyncListsCommand;
use App\Modules\OneC\Commands\OneCSyncServerListCommand;
use App\Modules\OneC\Jobs\SyncOneCListsJob;
use App\Modules\OneC\Jobs\SyncOneCServersListsJob;
use App\Modules\OneC\Jobs\SyncOneCServersUsers;
use App\Modules\OneC\Models\OneCInfoBaseUser;
use App\Modules\OneC\References\OneCInfoBaseReference;
use App\Modules\OneC\References\OneCInfoBaseUserReference;
use App\Support\Forms\RpcConnectionSettingsForm;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class OneCServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'onec';

    public function init(): void
    {
        $this->loadMigrations();

        $this->commands([
            OneCSyncListsCommand::class,
            OneCSyncServerListCommand::class,
            OneCSyncDatabaseUsersCommand::class,
        ]);

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(OneCInfoBaseReference::class);
        $references->register(OneCInfoBaseUserReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register(Indicator::fromArray([
            'module' => 'ONEC',
            'code' => 'ONEC_USER_COUNT',
            'type' => QueryExpressionType::class,
            'name' => 'Количество пользователей 1С',
            'expression' => new CountExpression(['column' => 'login']),
            'options' => [
                'model' => OneCInfoBaseUser::class,
                'query' => fn ($query) => $query->distinct()->select('login'),
            ]
        ]));
    }

    public function getOptions(): array
    {
        return [
            'name' => __('onec::messages.name'),
            'description' => __('onec::messages.description'),
            'view' => [
                'config' => [
                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.configuration'))->schema([
                            Section::make(__('onec::messages.rpc settings'))
                                ->statePath('base_list')
                                ->columns()
                                ->schema([
                                    Group::make(RpcConnectionSettingsForm::make(endpoint: '/fs/read'))
                                        ->columns()
                                        ->columnSpanFull(),

                                    Textarea::make('path')
                                        ->label(__('onec::messages.files path'))
                                        ->helperText(new HtmlString(__('onec::messages.files path help')))
                                        ->autosize()
                                        ->required()
                                        ->columnSpanFull(),
                                ]),

                            Grid::make()->schema([
                                /*
                                 * LISTS SYNC
                                 */
                                Section::make('1. '.__('onec::messages.job.list sync.title'))
                                    ->columnSpan(1)
                                    ->statePath('SyncList')
                                    ->schema([
                                        Toggle::make('enabled')
                                            ->label(__('admin.enabled'))
                                            ->helperText(__('onec::messages.job.list sync.description')),

                                        CronExpressionInput::make('schedule')
                                            ->hiddenLabel(),

                                        FormButton::make('runJobSyncList')
                                            ->label(__('admin.run'))
                                            ->action(fn () => $this->runJob(SyncOneCListsJob::class)),
                                    ]),

                                /*
                                 * SERVER LISTS SYNC
                                 */
                                Section::make('2. '.__('onec::messages.job.server list sync.title'))
                                    ->columnSpan(1)
                                    ->statePath('SyncServerList')
                                    ->schema([
                                        Toggle::make('enabled')
                                            ->label(__('admin.enabled'))
                                            ->helperText(__('onec::messages.job.server list sync.description')),

                                        CronExpressionInput::make('schedule')
                                            ->hiddenLabel(),

                                        FormButton::make('runJobSyncServerList')
                                            ->label(__('admin.run'))
                                            ->action(fn () => $this->runJob(SyncOneCServersListsJob::class)),
                                    ]),

                                /*
                                 * SERVER USERS SYNC
                                 */
                                Section::make('3. '.__('onec::messages.job.server users sync.title'))
                                    ->columnSpan(1)
                                    ->statePath('SyncServerUsers')
                                    ->schema([
                                        Toggle::make('enabled')
                                            ->label(__('admin.enabled'))
                                            ->helperText(__('onec::messages.job.server users sync.description')),

                                        CronExpressionInput::make('schedule')
                                            ->hiddenLabel(),

                                        FormButton::make('runJobSyncServerUsers')
                                            ->label(__('admin.run'))
                                            ->action(fn () => $this->runJob(SyncOneCServersUsers::class)),
                                    ]),
                            ])
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('onec::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new SyncOneCListsJob(), 'SyncList');
        $this->scheduleJob($schedule, new SyncOneCServersListsJob(), 'SyncServerList');
        $this->scheduleJob($schedule, new SyncOneCServersUsers(), 'SyncServerUsers');
    }

    public function runJob(ModuleScheduledJob|string $job): void
    {
        try {
            $job::dispatch();
            Notification::make()->success()->title(__('admin.job started'))->send();
        } catch (\Exception|\Error $e) {
            Notification::make()->danger()->title(__('admin.job staring error'))->body($e->getMessage())->send();
            Log::error($e);
        }
    }
}
