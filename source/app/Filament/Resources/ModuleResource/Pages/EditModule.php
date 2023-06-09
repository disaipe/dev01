<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Core\Module\Module;
use App\Filament\Resources\ModuleResource;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class EditModule extends EditRecord
{
    protected ?Module $module = null;

    protected bool $migrationsApplied = true;

    protected static string $resource = ModuleResource::class;

    protected function getTitle(): string
    {
        return $this->module->getName();
    }

    protected function getSubheading(): string|Htmlable|null
    {
        return trans_choice('admin.module', 1);
    }

    protected function getFormSchema(): array
    {
        $this->assertModule();

        return array_merge(
            $this->module->getOption('view.config', [])
        );
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('applyMigrations')
                ->label(__('admin.$module.migrate'))
                ->action('applyMigrations'),

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

        $this->checkMigrations();
    }

    protected function checkMigrations()
    {
        $finder = Finder::create();
        $finder
            ->files()
            ->name('*.php')
            ->in($this->module->getProvider()->getMigrationsDirectory(true));

        if ($finder->hasResults()) {
            $names = [];

            foreach ($finder->getIterator() as $file) {
                /** @var SplFileInfo $file */
                $names[] = $file->getFilenameWithoutExtension();
            }

            if (count($names)) {
                $migrations = DB::table('migrations')
                    ->whereIn('migration', $names)
                    ->distinct()
                    ->count();

                if (count($names) != $migrations) {
                    $this->migrationsApplied = false;
                }
            }
        }
    }

    public function applyMigrations()
    {
        $this->assertModule();

        Artisan::call('migrate', [
            '--path' => $this->module->getProvider()->getMigrationsDirectory(),
            '--force' => true,
        ]);

        Filament::notify('success', __('admin.$module.migrations started'));
    }
}
