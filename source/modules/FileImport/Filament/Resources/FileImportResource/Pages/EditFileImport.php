<?php

namespace App\Modules\FileImport\Filament\Resources\FileImportResource\Pages;

use App\Modules\FileImport\Filament\Resources\FileImportResource;
use App\Modules\FileImport\Jobs\FileImportJob;
use App\Modules\FileImport\Jobs\UserFileImportJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class EditFileImport extends EditRecord
{
    protected static string $resource = FileImportResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return trans_choice('fileimport::messages.file import', 1);
    }

    protected function getActions(): array
    {
        return [
            Action::make('import')
                ->label(__('fileimport::messages.action.file import.title'))
                ->tooltip(__('fileimport::messages.action.file import.tooltip'))
                ->action('importFile'),

            Action::make('selectAndImportFile')
                ->label(__('fileimport::messages.action.import from.title'))
                ->tooltip(__('fileimport::messages.action.import from.tooltip'))
                ->action('selectAndImportFile')
                ->form(function () {
                    $basePath = dirname($this->getRecord()->path);
                    $files = glob($basePath.'/*.{csv,xls,xlsx}', GLOB_BRACE);

                    $options = Arr::mapWithKeys($files, fn ($v) => [$v => basename($v)]);

                    return [
                        Select::make('path')
                            ->label(__('fileimport::messages.file'))
                            ->options($options)
                            ->required(),
                    ];
                }),

            DeleteAction::make(),
        ];
    }

    public function importFile(): void
    {
        FileImportJob::dispatch($this->getRecord()->getKey());
        Notification::make()->success()->title(__('fileimport::messages.action.file import.success'))->send();
    }

    public function selectAndImportFile($data): void
    {
        $path = Arr::get($data, 'path');

        if ($path) {
            UserFileImportJob::dispatch($this->getRecord()->getKey(), $path);
            Notification::make()->success()->title(__('fileimport::messages.action.import from.success'))->send();
        }
    }
}
