<?php

namespace App\Tables\Columns;

use App\Core\Module\Module;
use Filament\Tables\Columns\Column;

class ModuleNameColumn extends Column
{
    protected string $view = 'tables.columns.module-name-column';

    public function getModule(): Module
    {
        $record = $this->getRecord();

        return app('modules')->getByKey($record->key);
    }
}
