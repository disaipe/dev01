<?php

namespace App\Filament\Resources\IndicatorResource\Pages;

use App\Filament\Resources\IndicatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditIndicator extends EditRecord
{
    protected static string $resource = IndicatorResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return trans_choice('reference.Indicator', 1);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
