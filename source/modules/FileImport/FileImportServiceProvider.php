<?php

namespace App\Modules\FileImport;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Modules\FileImport\Commands\ImportFilesCommand;
use App\Modules\FileImport\Filament\Resources\FileImportResource;
use App\Modules\FileImport\Jobs\FileImportJob;
use App\Modules\FileImport\Models\FileImport;
use Filament\Facades\Filament;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Livewire\Livewire;

class FileImportServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'fileimport';

    public function onBooting()
    {
        Filament::registerResources([
            FileImportResource::class,
        ]);

        Livewire::component(
            'app.modules.file-import.filament.resources.file-import-resource.pages.create-file-import',
            FileImportResource\Pages\CreateFileImport::class,
        );

        Livewire::component(
            'app.modules.file-import.filament.resources.file-import-resource.pages.edit-file-import',
            FileImportResource\Pages\EditFileImport::class,
        );

        Livewire::component(
            'app.modules.file-import.filament.resources.file-import-resource.pages.list-file-imports',
            FileImportResource\Pages\ListFileImports::class,
        );
    }

    public function init(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/view', $this->namespace);

        $this->commands([
            ImportFilesCommand::class,
        ]);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('fileimport::messages.name'),
            'description' => __('fileimport::messages.description'),
            'view' => [
                'config' => [
                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('fileimport::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule)
    {
        $fileImports = FileImport::query()->enabled()->whereNotNull('options')->get();

        foreach ($fileImports as $fileImport) {
            /** @var FileImport $fileImport */
            $fileSchedule = Arr::get($fileImport->options, 'schedule');

            if ($fileSchedule) {
                $this->scheduleCronJob($schedule, new FileImportJob($fileImport->getKey()), $fileSchedule);
            }
        }
    }
}
