<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Core\Module\Module;
use App\Filament\Resources\ModuleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class EditModule extends EditRecord
{
    protected ?Module $module = null;

    protected bool $migrationsApplied = true;

    protected static string $resource = ModuleResource::class;

    public function getTitle(): string
    {
        return $this->module->getName();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return trans_choice('admin.module', 1);
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema())->columns(1);
    }

    protected function getFormSchema(): array
    {
        $this->assertModule();

        return array_merge(
            $this->module->getOption('view.config', [])
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyMigrations')
                ->label(__('admin.$module.migrate'))
                ->action('applyMigrations'),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->assertModule();
        $configData = $this->module->getConfig();

        $formFields = $this->form->getFlatFields();
        $defaultStates = Arr::mapWithKeys($formFields, function (Component $item, string $key) {
            return [$key => $item->getDefaultState()];
        }) ?? [];

        $dd = collect()->merge(Arr::undot($defaultStates))->merge($configData)->toArray();

        return parent::mutateFormDataBeforeFill($dd);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assertModule();
        $this->module->setConfig($data, true);

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function assertModule(): void
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

    protected function checkMigrations(): void
    {
        $directory = $this->module->getProvider()->getMigrationsDirectory(true);

        if (! is_dir($directory)) {
            return;
        }

        $finder = Finder::create();
        $finder
            ->files()
            ->name('*.php')
            ->in($directory);

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

    public function applyMigrations(): void
    {
        $this->assertModule();

        Artisan::call('migrate', [
            '--path' => $this->module->getProvider()->getMigrationsDirectory(),
            '--force' => true,
        ]);

        Notification::make()->success()->title(__('admin.$module.migrations started'))->send();
    }
}
