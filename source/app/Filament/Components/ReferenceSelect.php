<?php

namespace App\Filament\Components;

use App\Core\Reference\ReferenceEntry;
use Filament\Forms\Components\Select;
use Illuminate\Support\Arr;

class ReferenceSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $referencesOptions = static::getReferencesOptions();

        $this
            ->label(trans_choice('admin.reference', 1))
            ->options($referencesOptions);
    }

    protected static function getReferencesOptions(): array
    {
        $references = app('references')->getReferences();

        $referencesOptions = [];

        foreach ($references as $reference) {
            /** @var ReferenceEntry $reference */
            if ($reference->canAttachIndicators()) {
                $referencesOptions[$reference->getName()] = $reference->getLabel();
            }
        }

        return Arr::sort($referencesOptions);
    }
}
