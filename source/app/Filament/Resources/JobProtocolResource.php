<?php

namespace App\Filament\Resources;

use App\Core\Enums\JobProtocolState;
use App\Core\Module\Module;
use App\Core\Module\ModuleManager;
use App\Filament\Resources\JobProtocolResource\Pages;
use App\Filament\Resources\JobProtocolResource\Widgets\FailedJobsCount;
use App\Filament\Resources\JobProtocolResource\Widgets\QueueJobsCount;
use App\Models\JobProtocol;
use Carbon\CarbonInterface;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class JobProtocolResource extends Resource
{
    protected static ?string $model = JobProtocol::class;

    protected static ?string $navigationIcon = 'heroicon-o-rss';

    protected static ?int $navigationSort = 300;

    public static function table(Table $table): Table
    {
        /** @var ModuleManager $modules */
        $moduleManager = app('modules');
        $modules = $moduleManager->getModules();

        $modulesOptions = collect($modules)->reduce(function ($acc, Module $cur) {
            $acc[$cur->getKey()] = $cur->getName();

            return $acc;
        }, []);

        $table
            ->defaultSort('ended_at', 'desc')
            ->poll('10s');

        return $table
            ->columns([
                Tables\Columns\IconColumn::make('state')
                    ->label('')
                    ->size('md')
                    ->icons([
                        'heroicon-o-clock' => JobProtocolState::Create->value,
                        'heroicon-o-command-line' => JobProtocolState::Work->value,
                        'heroicon-o-exclamation-triangle' => JobProtocolState::Failed->value,
                        'heroicon-o-check-circle' => JobProtocolState::Ready->value,
                    ])
                    ->colors([
                        'danger' => JobProtocolState::Failed->value,
                        'success' => JobProtocolState::Ready->value,
                    ]),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.description'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('module')
                    ->label(trans_choice('admin.module', 1))
                    ->formatStateUsing(fn (string $state) => Arr::get($modulesOptions, $state, $state)),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label(__('admin.ended at'))
                    ->formatStateUsing(function (string $state, JobProtocol $record) {
                        $diff = $record->started_at->diffForHumans(
                            $record->ended_at,
                            CarbonInterface::DIFF_ABSOLUTE
                        );

                        return "{$state} ({$diff})";
                    })
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label(__('admin.state'))
                    ->options([
                        JobProtocolState::Create->value => __('common.JOB_PROTOCOL_STATE.C'),
                        JobProtocolState::Work->value => __('common.JOB_PROTOCOL_STATE.W'),
                        JobProtocolState::Ready->value => __('common.JOB_PROTOCOL_STATE.R'),
                        JobProtocolState::Failed->value => __('common.JOB_PROTOCOL_STATE.F'),
                    ]),

                Tables\Filters\SelectFilter::make('module')
                    ->label(trans_choice('admin.module', 1))
                    ->options($modulesOptions),
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('result')
                    ->icon('heroicon-s-eye')
                    ->label('')
                    ->action(fn () => $this->record->adance())
                    ->modalHeading(__('admin.result'))
                    ->modalContent(function ($record) {
                        return view(
                            'htmlable',
                            [
                                'getContent' => fn () => '<pre class="text-xs" style="white-space: break-spaces">'
                                    .json_encode($record->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                    .'</pre>',
                            ]
                        );
                    })
                    ->modalFooterActions([]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobProtocols::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            QueueJobsCount::class,
            FailedJobsCount::class,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getLabel(): ?string
    {
        return trans_choice('admin.job protocol', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('admin.job protocol', 2);
    }
}
