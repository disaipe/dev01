<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return trans_choice('admin.user', 1);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $password = Arr::get($data, 'password');

        if (! $password) {
            Arr::forget($data, 'password');
        }

        return $data;
    }
}
