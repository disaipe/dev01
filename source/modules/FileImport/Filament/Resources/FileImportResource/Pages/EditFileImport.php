<?php

namespace App\Modules\FileImport\Filament\Resources\FileImportResource\Pages;

use App\Modules\FileImport\Filament\Resources\FileImportResource;
use App\Modules\FileImport\Jobs\FileImportJob;
use App\Modules\FileImport\Jobs\UserFileImportJob;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class EditFileImport extends EditRecord
{
    protected static string $resource = FileImportResource::class;

    protected function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getSubheading(): string|Htmlable|null
    {
        return trans_choice('fileimport::messages.file import', 1);
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label(__('fileimport::messages.action.file import.title'))
                ->tooltip(__('fileimport::messages.action.file import.tooltip'))
                ->action('importFile'),

            Actions\Action::make('selectAndImportFile')
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

            Actions\DeleteAction::make(),
        ];
    }

    public function importFile(): void
    {
        FileImportJob::dispatch($this->getRecord()->getKey());
        Filament::notify('success', __('fileimport::messages.action.file import.success'));
    }

    public function selectAndImportFile($data): void
    {
        $path = Arr::get($data, 'path');

        if ($path) {
            UserFileImportJob::dispatch($this->getRecord()->getKey(), $path);
            Filament::notify('success', __('fileimport::messages.action.import from.success'));
        }
    }
}
