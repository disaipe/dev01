<?php

namespace App\Filament\Resources\CustomReferenceResource\Pages;

use App\Filament\Resources\CustomReferenceResource;
use App\Models\CustomReference;
use App\Services\CustomReferenceTableService;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

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
        $fields = Arr::get($data, 'schema.fields', []);
        $names = Arr::pluck($fields, 'name');

        if (! in_array('id', $names)) {
            array_unshift($fields, [
                'name' => 'id',
                'display_name' => 'ID',
                'type' => 'bigint',
                'unsigned' => true,
                'pk' => true,
                'autoincrement' => true,
                'nullable' => false,
                'readonly' => true,
            ]);
        }

        if (! in_array('created_at', $names)) {
            $fields[] = [
                'name' => 'created_at',
                'display_name' => 'Создано',
                'type' => 'datetime',
                'readonly' => true,
            ];
        }

        if (! in_array('updated_at', $names)) {
            $fields[] = [
                'name' => 'updated_at',
                'display_name' => 'Изменено',
                'type' => 'datetime',
                'readonly' => true,
            ];
        }

        if (! in_array('deleted_at', $names)) {
            $fields[] = [
                'name' => 'deleted_at',
                'display_name' => 'Удалено',
                'type' => 'datetime',
                'readonly' => true,
            ];
        }

        Arr::set($data, 'schema.fields', $fields);

        return $data;
    }

    public function syncReferenceTable(CustomReferenceTableService $tableService): void
    {
        /** @var CustomReference $reference */
        $reference = $this->getRecord();
        $tableService->sync($reference);
    }
}
