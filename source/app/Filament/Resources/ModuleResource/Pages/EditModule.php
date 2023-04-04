<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Core\Module\Module;
use App\Filament\Resources\ModuleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModule extends EditRecord
{
    protected ?Module $module = null;

    protected static string $resource = ModuleResource::class;

    protected function getFormSchema(): array
    {
        $this->assertModule();

        return array_merge(
            parent::getFormSchema(),
            $this->module->getOption('view.config', [])
        );
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->assertModule();
        $configData = $this->module->getConfig();

        return parent::mutateFormDataBeforeFill($configData);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assertModule();
        $this->module->setConfig($data, true);

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function assertModule()
    {
        if ($this->module) {
            return;
        }

        $this->module = app('modules')->getBykey($this->record->key);

        if (! $this->module) {
            throw new \Exception('Module not found');
        }
    }
}
