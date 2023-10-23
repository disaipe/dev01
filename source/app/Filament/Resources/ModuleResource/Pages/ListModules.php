<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Core\Module\ModuleManager;
use App\Filament\Resources\ModuleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        /** @var ModuleManager $modules */
        $modules = app('modules');
        $modulesKeys = array_keys($modules->getModules());

        return parent::getTableQuery()->whereIn('key', $modulesKeys);
    }
}
