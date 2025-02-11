<?php

namespace App\Modules\Sharepoint\Filament\Resources;

use App\Core\Module\Module;
use App\Filament\Components\CronExpressionInput;
use App\Forms\Components\RawHtmlContent;
use App\Models\CustomReference;
use App\Modules\Sharepoint\Filament\Resources\SharepointListResource\Pages;
use App\Modules\Sharepoint\Jobs\SyncSharepointListJob;
use App\Modules\Sharepoint\Models\SharepointList;
use App\Modules\Sharepoint\Services\SharepointService;
use Cron\CronExpression;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class SharepointListResource extends Resource
{
    protected static ?string $model = SharepointList::class;

    protected static ?string $navigationIcon = 'heroicon-s-bars-4';

    public static function shouldRegisterNavigation(): bool
    {
        if (! Schema::hasTable('sharepoint_lists')) {
            return false;
        }

        /** @var Module $module */
        $module = app('modules')->getByKey('sharepoint');

        if (! $module?->isEnabled()) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    public static function form(Form $form): Form
    {
        $sharepointService = new SharepointService();

        return $form
            ->schema([
                RawHtmlContent::make(function (?SharepointList $record = null) {
                    if (! $record) {
                        return '';
                    }

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

                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('list_name')
                            ->label(__('sharepoint::messages.list name'))
                            ->reactive()
                            ->required(),

                        Forms\Components\TextInput::make('list_site')
                            ->label(__('sharepoint::messages.list site')),

                        Forms\Components\Select::make('custom_reference_id')
                            ->label(__('sharepoint::messages.reference'))
                            ->helperText(__('sharepoint::messages.reference help'))
                            ->relationship('reference', 'display_name')
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(SharepointListResource::getCustomReference(...))
                            ->afterStateUpdated(SharepointListResource::getCustomReference(...)),

                        Forms\Components\Select::make('options.company_prefix.field')
                            ->label(__('sharepoint::messages.company prefix field'))
                            ->helperText(__('sharepoint::messages.company prefix field help'))
                            ->required()
                            ->options(function ($get, $set) use ($sharepointService) {
                                $result = [];

                                $listName = $get('list_name');
                                $listSite = $get('list_site');

                                if ($listName) {
                                    $sharepointList = $sharepointService->getList($listName, $listSite);

                                    if ($sharepointList) {
                                        $fields = $sharepointList->getFields();

                                        if ($fields) {
                                            $visibleFields = Arr::where($fields, fn ($field) => Arr::get($field, 'Hidden') !== 'TRUE');
                                            $options = Arr::pluck($visibleFields, 'DisplayName', 'ColName');

                                            $set('__list_fields', $options);

                                            return $options;
                                        }
                                    }
                                }

                                $set('__list_fields', []);

                                return $result;
                            }),
                    ]),

                    CronExpressionInput::make('options.schedule')
                        ->label(__('admin.schedule')),

                    Forms\Components\Toggle::make('enabled')
                        ->label(__('admin.enabled')),
                ]),

                Forms\Components\Section::make(__('sharepoint::messages.fields schema.title'))
                    ->visible(fn ($get) => (bool) $get('custom_reference_id'))
                    ->schema([
                        Forms\Components\Repeater::make('options.fields')
                            ->hiddenLabel()
                            ->columns()
                            ->schema([
                                Forms\Components\Select::make('ref')
                                    ->label(__('sharepoint::messages.fields schema.reference'))
                                    ->helperText(__('sharepoint::messages.fields schema.reference help'))
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

                                Forms\Components\Select::make('field')
                                    ->label(__('sharepoint::messages.fields schema.list'))
                                    ->helperText(__('sharepoint::messages.fields schema.list help'))
                                    ->options(function ($get) {
                                        $options = $get('../../../__list_fields');

                                        return $options ?? [];
                                    }),
                            ])
                            ->addActionLabel(__('sharepoint::messages.fields schema.add'))
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
                        ->label(__('sharepoint::messages.action.sync list.title'))
                        ->tooltip(__('sharepoint::messages.action.sync list.tooltip'))
                        ->icon('heroicon-s-play')
                        ->action(fn (SharepointList $record) => self::syncList($record)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSharepointLists::route('/'),
            'create' => Pages\CreateSharepointList::route('/create'),
            'edit' => Pages\EditSharepointList::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans_choice('sharepoint::messages.sharepoint list', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('sharepoint::messages.sharepoint list', 2);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    protected static function getCustomReference($state, $set): void
    {
        if ($state) {
            /** @var CustomReference $cr */
            $customReference = CustomReference::query()->whereKey($state)->first();
            $set('__hasCompanyContext', $customReference?->company_context);
        } else {
            $set('__hasCompanyContext', false);
        }
    }

    protected static function syncList(SharepointList $record): void
    {
        SyncSharepointListJob::dispatch($record->getKey());
        Notification::make()->success()->title(__('sharepoint::messages.action.sync list.success'))->send();
    }
}
