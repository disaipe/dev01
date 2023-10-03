<?php

namespace App\Modules\Directum;

use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Report\Expression\CountExpression;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Modules\Directum\Commands\DirectumSyncUsersCommand;
use App\Modules\Directum\Jobs\DirectumSyncUsersJob;
use App\Modules\Directum\Models\DirectumUser;
use App\Support\Forms\SqlConnectionSettingsForm;
use App\Support\SqlServerConnection;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DirectumServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'directum';

    public const CONNECTION_NAME = 'directum';

    public function init(): void
    {
        $this->loadMigrations();
        $this->setupDatabaseConnection();

        $this->commands([
            DirectumSyncUsersCommand::class,
        ]);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register(Indicator::fromArray([
            'module' => $this->namespace,
            'code' => 'DIRECTUM_USERS_COUNT',
            'name' => 'Количество активных пользователей Directum',
            'model' => DirectumUser::class,
            'query' => fn (Builder $query) => $query->active(),
            'expression' => new CountExpression(),
        ]));
    }

    public function getOptions(): array
    {
        return [
            'name' => __('directum::messages.name'),
            'description' => __('directum::messages.description'),
            'casts' => [
                'db_password' => 'password',
            ],
            'view' => [
                'config' => [
                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.configuration'))->schema([
                            Section::make(__('directum::messages.connection settings'))
                                ->columns()
                                ->schema(SqlConnectionSettingsForm::make('', function ($get) {
                                    $this->setupDatabaseConnection($get);
                                })),

                            Section::make(__('directum::messages.job.users.title'))
                                ->schema([
                                    RawHtmlContent::make(__('directum::messages.job.users.description')),

                                    CronExpressionInput::make('SyncUsers.schedule')
                                        ->label(__('admin.schedule')),

                                    Checkbox::make('SyncUsers.enabled')
                                        ->label(__('admin.enabled')),

                                    FormButton::make('runAllServersJob')
                                        ->label(__('admin.run'))
                                        ->action(fn () => $this->runSyncUsersJob()),
                                ]),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('directum::help'),
                        ]),
                    ]),
                ]
            ]
        ];
    }

    public function setupDatabaseConnection($get = null): void
    {
        $config = $this->module->getConfig();

        if ($get) {
            foreach ($config as $key => $value) {
                Arr::set($config, $key, $get($key));
            }
        }

        SqlServerConnection::Setup(static::CONNECTION_NAME, $config);
    }

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new DirectumSyncUsersJob(), 'SyncUsers');
    }

    public function runSyncUsersJob(): void
    {
        try {
            DirectumSyncUsersJob::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }
}
