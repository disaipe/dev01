<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ScheduledJobListWidget extends TableWidget
{
    protected int | string | array $columnSpan = 2;

    /**
     * @return string|null
     */
    public function getTableHeading(): ?string
    {
        return __('admin.$schedule.widget.title');
    }

    protected function getTableQuery(): Builder|Relation
    {
        return Job::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('payload.displayName')
                ->label(__('admin.name'))
                ->formatStateUsing(fn ($record) => $record->getName()),

            TextColumn::make('attempts')
                ->label(__('admin.$schedule.widget.attempts')),

            TextColumn::make('available_at')
                ->label(__('admin.$schedule.widget.available at')),

            TextColumn::make('reserved_at')
                ->label(__('admin.$schedule.widget.reserved at')),
        ];
    }

    protected function getTablePollingInterval(): ?string
    {
        return '10s';
    }
}
