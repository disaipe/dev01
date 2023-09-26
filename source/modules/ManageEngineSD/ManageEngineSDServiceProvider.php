<?php

namespace App\Modules\ManageEngineSD;

use App\Core\Enums\ReportContextConstant;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\CountExpression;
use App\Core\Report\Expression\ExpressionManager;
use App\Facades\Config;
use App\Modules\ManageEngineSD\Models\SDStatusDefinition;
use App\Modules\ManageEngineSD\Models\SDWorkorder;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ManageEngineSDServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'mesd';

    public function init(): void
    {
        $this->setupDatabaseConnection();

        $this->loadMigrations();
        $this->loadViews();

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(WorkorderReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register(Indicator::fromArray([
            'module' => $this->namespace,
            'code' => 'MESD_TOTAL_REQUESTS_COUNT',
            'name' => '[MESD] Общее количество созданных заявок от организации',
            'model' => SDWorkorder::class,
            'expression' => new CountExpression(),
            'scopes' => [
                'period' => function (Builder $query, array $context) {
                    $period = Arr::get($context, ReportContextConstant::PERIOD->name);

                    if (get_class($period) === Carbon::class) {
                        $from = $period->copy()->startOfMonth();
                        $to = $period->copy()->endOfMonth();

                        $query->creationPeriod($from, $to);
                    }
                }
            ]
        ]));
    }

    public function onBooting(): void
    {
        /** @var ExpressionManager $expressions */
        $expressions = app('expressions');
        $expressions->register(SDServiceTimeExpression::class);
        $expressions->register(SDServiceCountExpression::class);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('mesd::messages.name'),
            'description' => __('mesd::messages.description'),
            'casts' => [
                'db_password' => 'password',
                'closes_statuses' => 'json',
            ],
            'view' => [
                'config' => [
                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.configuration'))->schema([
                            Section::make(__('mesd::messages.connection settings'))->columns()->schema([
                                TextInput::make('db_host')
                                    ->label(__('mesd::messages.host'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($get) {
                                        $this->setupDatabaseConnection($get);
                                    }),

                                TextInput::make('db_port')
                                    ->label(__('mesd::messages.port'))
                                    ->numeric()
                                    ->required(),

                                TextInput::make('db_username')
                                    ->label(__('mesd::messages.login')),

                                TextInput::make('db_password')
                                    ->label(__('mesd::messages.password'))
                                    ->password(),

                                TextInput::make('db_name')
                                    ->label(__('mesd::messages.database')),

                                Select::make('sslmode')
                                    ->label('SSL')
                                    ->options([
                                        'disable' => 'Disable',
                                        'allow' => 'Allow',
                                        'prefer' => 'Prefer',
                                        'require' => 'Require',
                                    ]),
                            ]),

                            Section::make(__('mesd::messages.settings'))->schema([
                                Select::make('closes_statuses')
                                    ->label(__('mesd::messages.closed statuses'))
                                    ->helperText(__('mesd::messages.closed statuses help'))
                                    ->multiple()
                                    ->options(function () {
                                        try {
                                            return SDStatusDefinition::query()
                                                ->get()
                                                ->pluck('statusname', 'statusid');
                                        } catch (\Exception) {
                                            return [];
                                        }
                                    }),
                            ]),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('mesd::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    protected function setupDatabaseConnection($get = null): void
    {
        $config = $this->module->getConfig();
        Arr::set(
            $config,
            'db_password',
            Config::decryptValue(Arr::get($config, 'db_password'))
        );

        if ($get) {
            foreach ($config as $key => $value) {
                Arr::set($config, $key, $get($key));
            }
        }

        SDConnection::Setup($config);
    }
}
