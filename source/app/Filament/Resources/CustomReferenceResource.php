<?php

namespace App\Filament\Resources;

use App\Core\Enums\CustomReferenceContextType;
use App\Filament\Resources\CustomReferenceResource\Pages;
use App\Models\CustomReference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class CustomReferenceResource extends Resource
{
    protected static ?string $model = CustomReference::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Forms\Components\Section::make('Базовая конфигурация')
                    ->columns()
                    ->schema([
                        Forms\Components\TextInput::make('display_name')
                            ->label(__('admin.name'))
                            ->helperText(new HtmlString('Наименование справочника для отображения пользователям, например <i>"Виртуальные машины"</i>'))
                            ->maxLength(128)
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.short name'))
                            ->helperText(new HtmlString('Техническое наименование справочника, например <i>"VirtualMachine"</i>. <b>После создания изменить нельзя!</b>'))
                            ->maxLength(32)
                            ->disabled(fn ($record) => isset($record))
                            ->required(),

                        Forms\Components\TextInput::make('label')
                            ->label('Наименование записи (единственное число)')
                            ->helperText(new HtmlString('Например <i>"Виртуальная машина"</i>'))
                            ->maxLength(64)
                            ->required(),

                        Forms\Components\TextInput::make('plural_label')
                            ->label('Наименование записи (множественное число)')
                            ->helperText(new HtmlString('Например <i>"Виртуальные машины"</i>'))
                            ->maxLength(64)
                            ->required(),

                        Forms\Components\Group::make([
                            Forms\Components\Toggle::make('company_context')
                                ->label('Контекст организации')
                                ->helperText('Добавить колонку организации для создания привязки записи справочника к организации')
                                ->reactive()
                                ->default(true),

                            Forms\Components\TextInput::make('icon')
                                ->label('Иконка')
                                ->helperText(new HtmlString('Укажите код иконки из <a class="underline" href="https://icones.js.org" target="_blank">https://icones.js.org</a>')),
                        ])
                            ->columns(1),

                        Forms\Components\Select::make('context_type')
                            ->label('Значение организации')
                            ->helperText(
                                'Укажите тип хранения контекста организации - по коду или идентификатору. Предпочтительнее использовать'
                                .' идентификатор, но при необходимости (например, при интеграции из сторонних систем)'
                                .' можете использовать код организации.'
                            )
                            ->selectablePlaceholder(false)
                            ->options([
                                CustomReferenceContextType::Id->value => 'По id (будет автоматически создана колонка <span class="font-bold">`company_id`</span>)',
                                CustomReferenceContextType::Code->value => 'По коду (будет автоматически создана колонка <span class="font-bold">`company_code`</span>)',
                            ])
                            ->allowHtml()
                            ->default(CustomReferenceContextType::Code->value)
                            ->visible(fn (Get $get) => (bool) $get('company_context'))
                            ->required(fn (Get $get) => (bool) $get('company_context')),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Активно')
                            ->helperText('Справочник настроен и готов к работе')
                            ->default(false)
                            ->offColor(Color::Red)
                            ->onColor(Color::Green)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Конфигурация полей')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\View::make('admin.help.customReferenceFields'),

                        Forms\Components\Repeater::make('schema.fields')
                            ->label('')
                            ->columnSpanFull()
                            ->columns(5)
                            ->addActionLabel('Добавить поле')
                            ->itemLabel(fn ($state) => Arr::get($state, 'display_name'))
                            ->schema([
                                Forms\Components\TextInput::make('display_name')
                                    ->label('Заголовок')
                                    ->required(),

                                Forms\Components\TextInput::make('name')
                                    ->label('Наименование поля')
                                    ->readOnly($isSystem)
                                    ->extraInputAttributes(fn ($get) => ['disabled' => $isSystem($get)])
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
                                    ->extraInputAttributes(fn ($get) => ['disabled' => $isSystem($get)])
                                    ->required(),

                                Forms\Components\TextInput::make('length')
                                    ->label('Длина')
                                    ->visible(fn ($get) => $get('type') === 'string')
                                    ->readOnly($isSystem)
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
                    ->label(__('admin.name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.short name')),

                Tables\Columns\ToggleColumn::make('enabled')
                    ->label(__('admin.enabled')),
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

    public static function getNavigationGroup(): ?string
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
