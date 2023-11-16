<?php

namespace App\Modules\Sharepoint;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Modules\Sharepoint\Filament\Resources\SharepointListResource;
use App\Modules\Sharepoint\Jobs\SyncSharepointListJob;
use App\Modules\Sharepoint\Models\SharepointList;
use App\Modules\Sharepoint\Utils\SharepointConnection;
use App\Support\Forms\SqlConnectionSettingsForm;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Livewire\Livewire;

class SharepointServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'sharepoint';

    public function onBooting(): void
    {
        Filament::registerResources([
            SharepointListResource::class,
        ]);

        Livewire::component(
            'app.modules.sharepoint.filament.resources.sharepoint-list-resource.pages.create-sharepoint-list',
            SharepointListResource\Pages\CreateSharepointList::class,
        );

        Livewire::component(
            'app.modules.sharepoint.filament.resources.sharepoint-list-resource.pages.edit-sharepoint-list',
            SharepointListResource\Pages\EditSharepointList::class
        );

        Livewire::component(
            'app.modules.sharepoint.filament.resources.sharepoint-list-resource.pages.list-sharepoint-lists',
            SharepointListResource\Pages\ListSharepointLists::class
        );
    }

    public function init(): void
    {
        $this->loadMigrations();
        $this->setupDatabaseConnection();
    }

    public function getOptions(): array
    {
        return [
            'name' => __('sharepoint::messages.name'),
            'description' => __('sharepoint::messages.description'),
            'casts' => [
                'db_password' => 'password',
                'closes_statuses' => 'json',
            ],
            'view' => [
                'config' => [
                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.configuration'))->schema([
                            Section::make(__('sharepoint::messages.connection settings'))
                                ->columns()
                                ->schema(SqlConnectionSettingsForm::make('', function ($get) {
                                    $this->setupDatabaseConnection($get);
                                })),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('sharepoint::help'),
                        ]),
                    ]),
                ],
            ],
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

        $savedConfig = SharepointConnection::Config();
        $config = array_merge($savedConfig, $config);

        SharepointConnection::setup('sharepoint', $config);
    }

    public function schedule(Schedule $schedule): void
    {
        $sharepointLists = SharepointList::query()->enabled()->whereNotNull('options')->get();

        foreach ($sharepointLists as $sharepointList) {
            /** @var SharepointList $sharepointList */
            $listSchedule = Arr::get($sharepointList->options, 'schedule');

            if ($listSchedule) {
                $this->scheduleCronJob($schedule, new SyncSharepointListJob($sharepointList->getKey()), $listSchedule);
            }
        }
    }
}
