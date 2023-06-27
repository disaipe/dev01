<?php

namespace App\Modules\ManageEngineSD;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Report\Expression\ExpressionManager;
use App\Facades\Config;
use App\Modules\ManageEngineSD\Models\SDStatusDefinition;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Support\Arr;

class ManageEngineSDServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'mesd';

    public function init(): void
    {
        $this->setupDatabaseConnection();

        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/view', $this->namespace);

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(WorkorderReference::class);
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
                                    ->numeric(),

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
