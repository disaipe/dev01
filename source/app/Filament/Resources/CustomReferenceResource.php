<?php

namespace App\Filament\Resources;

use App\Core\Enums\CustomReferenceContextType;
use App\Filament\Resources\CustomReferenceResource\Pages;
use App\Models\CustomReference;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class CustomReferenceResource extends Resource
{
    protected static ?string $model = CustomReference::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        $disabled = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $isSystem = fn ($get) => in_array($get('name'), $disabled);

        return $form
            ->schema([
                Forms\Components\Section::make('Базовая конфигурация')->schema([
                    Forms\Components\TextInput::make('display_name')
                        ->label(__('admin.name'))
                        ->helperText('Наименование справочника для отображения пользователям, например <i>"Виртуальные машины"</i>')
                        ->maxLength(128)
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.short name'))
                        ->helperText('Техническое наименование справочника, например <i>"VirtualMachine"</a>. <b>После создания изменить нельзя!</b>')
                        ->maxLength(32)
                        ->disabled(fn ($record) => isset($record))
                        ->required(),

                    Forms\Components\TextInput::make('label')
                        ->label('Наименование записи (единственное число)')
                        ->helperText('Например <i>"Виртуальная машина"</i>')
                        ->maxLength(64)
                        ->required(),

                    Forms\Components\TextInput::make('plural_label')
                        ->label('Наименование записи (множественное число)')
                        ->helperText('Например <i>"Виртуальные машины"</i>')
                        ->maxLength(64)
                        ->required(),

                    Forms\Components\Grid::make()->schema([
                        Forms\Components\Toggle::make('company_context')
                            ->label('Контекст организации')
                            ->helperText('Добавить колонку организации для создания привязки записи справочника к организации')
                            ->default(true),

                        Forms\Components\Select::make('context_type')
                            ->label('Значение организации')
                            ->helperText(
                                'Укажите тип хранения контекста организации - по коду или идентификатору. Предпочтительнее использовать'
                                . ' идентификатор, но при необходимости (например, при интеграции из сторонних систем)'
                                . ' можете использовать префикс организации.'
                            )
                            ->disablePlaceholderSelection()
                            ->options([
                                CustomReferenceContextType::Id->value => 'По id (company_id)',
                                CustomReferenceContextType::Code->value => 'По коду (company_code)',
                            ]),
                    ]),

                    Forms\Components\Toggle::make('enabled')
                        ->label('Активно')
                        ->helperText('Справочник настроен и готов к работе')
                        ->default(false)
                        ->columnSpanFull(),
                ])
                    ->columns(2),

                Forms\Components\Section::make('Конфигурация полей')
                    ->schema([
                        Forms\Components\View::make('admin.help.customReferenceFields'),

                        Forms\Components\Repeater::make('schema.fields')
                            ->label('')
                            ->columnSpanFull()
                            ->columns(5)
                            ->createItemButtonLabel('Добавить поле')
                            ->schema([
                                Forms\Components\TextInput::make('display_name')
                                    ->label('Заголовок')
                                    ->required(),

                                Forms\Components\TextInput::make('name')
                                    ->label('Наименование поля')
                                    ->disabled($isSystem)
                                    ->required(),

                                Forms\Components\Select::make('type')
                                    ->label('Тип')
                                    ->options([
                                        'string' => 'String',
                                        'int' => 'Integer',
                                        'bigint' => 'Big integer',
                                        'float' => 'Float',
                                        'boolean' => 'Boolean',
                                        'date' => 'Date',
                                        'datetime' => 'Datetime',
                                    ])
                                    ->reactive()
                                    ->disabled($isSystem)
                                    ->required(),

                                Forms\Components\TextInput::make('length')
                                    ->label('Длина')
                                    ->visible(fn ($get) => $get('type') === 'string')
                                    ->disabled($isSystem)
                                    ->default(255)
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),

                                Forms\Components\Toggle::make('required')
                                    ->label('Обязательное')
                                    ->disabled($isSystem)
                                    ->inline(false)
                                    ->default(false),
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label(__('admin.name')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.short name')),

                Tables\Columns\ToggleColumn::make('enabled')
                    ->label(__('admin.enabled')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomReferences::route('/'),
            'create' => Pages\CreateCustomReference::route('/create'),
            'edit' => Pages\EditCustomReference::route('/{record}/edit'),
        ];
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    public static function getLabel(): ?string
    {
        return trans_choice('reference.CustomReference', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('reference.CustomReference', 2);
    }
}
