<?php

namespace App\Modules\FileImport\Filament\Resources;

use App\Filament\Components\CronExpressionInput;
use App\Forms\Components\RawHtmlContent;
use App\Models\CustomReference;
use App\Modules\FileImport\Filament\Resources\FileImportResource\Pages;
use App\Modules\FileImport\FileImportCompanySyncType;
use App\Modules\FileImport\Models\FileImport;
use App\Modules\FileImport\Services\FileImportService;
use Cron\CronExpression;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class FileImportResource extends Resource
{
    protected static ?string $model = FileImport::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-text';

    protected static function shouldRegisterNavigation(): bool
    {
        if (! Schema::hasTable('file_imports')) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RawHtmlContent::make(function (FileImport $record) {
                    $out = '';

                    if ($record->last_sync) {
                        $out = '<div class="text-right text-sm">'.
                            __('admin.last sync date', ['date' => $record->last_sync]).
                            '</div>';
                    }

                    $syncEnabled = $record->enabled;
                    $schedule = Arr::get($record->options, 'schedule');

                    if ($syncEnabled && $schedule) {
                        $expr = new CronExpression($schedule);
                        $nextDate = $expr->getNextRunDate();
                        $nextDateStr = $nextDate->format('Y-m-d H:i:s');

                        $out .= '<div class="text-right text-sm">'.
                            __('admin.next sync date', ['date' => $nextDateStr]).
                            '</div>';
                    }

                    return $out;
                })
                    ->columnSpanFull(),

                Forms\Components\Section::make(__('admin.common'))->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.name'))
                        ->required()
                        ->maxLength(256)
                        ->columnSpanFull()
                        ->reactive(),

                    Forms\Components\TextInput::make('path')
                        ->label(__('fileimport::messages.path'))
                        ->helperText(__('fileimport::messages.path help'))
                        ->required()
                        ->suffixIcon('heroicon-s-check')
                        ->maxLength(1024)
                        ->reactive()
                        ->afterStateHydrated(FileImportResource::getFileHeaders(...))
                        ->afterStateUpdated(FileImportResource::getFileHeaders(...))
                        ->columnSpanFull(),

                    Forms\Components\Select::make('custom_reference_id')
                        ->label(__('fileimport::messages.reference'))
                        ->helperText(__('fileimport::messages.reference help'))
                        ->relationship('reference', 'display_name')
                        ->required()
                        ->reactive()
                        ->afterStateHydrated(FileImportResource::getCustomReference(...))
                        ->afterStateUpdated(FileImportResource::getCustomReference(...))
                        ->columnSpanFull(),

                    Forms\Components\Fieldset::make(__('fileimport::messages.company linking'))
                        ->visible(fn ($get) => $get('__hasCompanyContext'))
                        ->schema([
                            Forms\Components\Select::make('options.company_prefix.type')
                                ->label(__('fileimport::messages.fields schema.company prefix type'))
                                ->helperText(__('fileimport::messages.fields schema.company prefix type help'))
                                ->options([
                                    FileImportCompanySyncType::Id->value => __('fileimport::messages.fields schema.by company id'),
                                    FileImportCompanySyncType::Code->value => __('fileimport::messages.fields schema.by company code'),
                                ])
                                ->required(),

                            Forms\Components\Select::make('options.company_prefix.field')
                                ->label(__('fileimport::messages.fields schema.company prefix column'))
                                ->helperText(__('fileimport::messages.fields schema.company prefix column help'))
                                ->options(fn ($get) => $get('__headers') ?? [])
                                ->required(),
                        ]),

                    CronExpressionInput::make('options.schedule')
                        ->label(__('admin.schedule')),

                    Forms\Components\Toggle::make('enabled')
                        ->label(__('admin.enabled')),
                ]),

                Forms\Components\Section::make(__('fileimport::messages.fields schema.title'))
                    ->visible(fn ($get) => (bool) $get('custom_reference_id'))
                    ->schema([
                        Forms\Components\Repeater::make('options.fields')
                            ->disableLabel()
                            ->columns()
                            ->schema([
                                Forms\Components\Select::make('ref')
                                    ->label(__('fileimport::messages.fields schema.reference'))
                                    ->helperText(__('fileimport::messages.fields schema.reference help'))
                                    ->required()
                                    ->options(function ($get) {
                                        $referenceId = $get('../../../custom_reference_id');

                                        if ($referenceId) {
                                            if ($cr = CustomReference::query()->whereKey($referenceId)->first()) {
                                                return Arr::pluck($cr->getFields(), 'display_name', 'name');
                                            }
                                        }

                                        return [];
                                    }),

                                Forms\Components\Select::make('file')
                                    ->label(__('fileimport::messages.fields schema.file'))
                                    ->helperText(__('fileimport::messages.fields schema.file help'))
                                    ->options(fn ($get) => $get('../../../__headers') ?? [])
                                    ->required(),
                            ])
                            ->createItemButtonLabel(__('fileimport::messages.fields schema.add'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name')),

                Tables\Columns\ToggleColumn::make('enabled')
                    ->label(__('admin.enabled')),

                Tables\Columns\TextColumn::make('last_sync')
                    ->label(__('admin.last sync')),
            ])->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('import')
                        ->label(__('fileimport::messages.action.file import.title'))
                        ->tooltip(__('fileimport::messages.action.file import.tooltip'))
                        ->icon('heroicon-s-play')
                        ->action('importFile'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFileImports::route('/'),
            'create' => Pages\CreateFileImport::route('/create'),
            'edit' => Pages\EditFileImport::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans_choice('fileimport::messages.file import', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('fileimport::messages.file import', 2);
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    protected static function getCustomReference($state, $set)
    {
        if ($state) {
            /** @var CustomReference $cr */
            $customReference = CustomReference::query()->whereKey($state)->first();
            $set('__hasCompanyContext', $customReference?->company_context);
        } else {
            $set('__hasCompanyContext', false);
        }
    }

    /**
     * Parse file and get headers from them
     *
     * @param  TextInput  $component
     */
    public static function getFileHeaders(Forms\Components\TextInput $component, $state, $set): void
    {
        $headers = [];

        try {
            if (File::exists($state)) {
                $file = FileImportService::make($state);
                $headers = $file->getHeaders();

                $headers = array_reduce($headers, function ($acc, $cur) {
                    $acc[$cur] = $cur;

                    return $acc;
                }, []);
            }
        } catch (\Exception $e) {
        }

        $set('__headers', $headers);

        $component->suffixIcon(count($headers) ? 'heroicon-s-check' : 'heroicon-s-x-circle');
    }
}
