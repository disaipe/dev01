<?php

namespace App\Filament\Resources\IndicatorResource\Pages;

use App\Filament\Resources\IndicatorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditIndicator extends EditRecord
{
    protected static string $resource = IndicatorResource::class;

    protected function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getSubheading(): string|Htmlable|null
    {
        return trans_choice('reference.Indicator', 1);
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
