<?php

namespace App\Filament\Resources\CustomReferenceResource\Pages;

use App\Filament\Resources\CustomReferenceResource;
use App\Models\CustomReference;
use App\Services\CustomReferenceTableService;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCustomReference extends EditRecord
{
    protected static string $resource = CustomReferenceResource::class;

    protected function getTitle(): string
    {
        return $this->record->display_name;
    }

    protected function getSubheading(): string|Htmlable|null
    {
        return trans_choice('admin.reference', 1);
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('Синхронизировать')
                ->tooltip('Применить настройки полей к структуре таблицы базы данных')
                ->action('syncReferenceTable')
                ->requiresConfirmation()
                ->modalHeading('Синхронизация структуры БД')
                ->modalSubheading('Сейчас будет производится настройка структуры таблицы базы данных,'
                    .' исходя из указанных настроек. В некоторых случаях возможна потеря данных, продолжить?')
                ->modalButton('Я понимаю'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CustomReferenceTableService::preserveSchemaFields($data);
    }

    public function syncReferenceTable(CustomReferenceTableService $tableService): void
    {
        /** @var CustomReference $reference */
        $reference = $this->getRecord();
        $tableService->sync($reference);
    }
}
