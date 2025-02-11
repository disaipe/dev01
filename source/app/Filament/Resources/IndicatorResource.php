<?php

namespace App\Filament\Resources;

use App\Core\Indicator\IndicatorManager;
use App\Core\Report\ExpressionType\IndicatorSumExpressionType;
use App\Core\Report\ExpressionType\QueryExpressionType;
use App\Filament\Resources\IndicatorResource\Pages;
use App\Forms\Components\RawHtmlContent;
use App\Models\Company;
use App\Models\ExpressionType;
use App\Models\Indicator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class IndicatorResource extends Resource
{
    protected static ?string $model = Indicator::class;

    protected static ?string $navigationIcon = 'heroicon-o-variable';

    public static function form(Form $form): Form
    {
        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $mutators = $indicators->getMutators();
        $mutatorOptions = $mutators->get()->mapWithKeys(fn ($item, $key) => [$key => __("mutator.$key")]);

        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.$indicator.common'))
                    ->icon('heroicon-o-bars-3')
                    ->columns()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.name'))
                            ->maxLength(256)
                            ->required(),

                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.code'))
                            ->regex('/[a-zA-Z_-]+/')
                            ->maxLength(32)
                            ->disabled(fn ($record) => isset($record)),

                        Forms\Components\Select::make('type')
                            ->label(__('admin.type'))
                            ->options([
                                class_basename(QueryExpressionType::class) => QueryExpressionType::label(),
                                class_basename(IndicatorSumExpressionType::class) => IndicatorSumExpressionType::label(),
                            ])
                            ->default(QueryExpressionType::class)
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('indicator_group_id')
                            ->relationship('group', 'name')
                            ->label(__('admin.group'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.name'))
                                    ->required(),

                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('admin.color')),
                            ]),

                        Forms\Components\Toggle::make('published')
                            ->label(__('admin.enabled')),
                    ]),

                Forms\Components\Section::make(__('admin.$indicator.schema'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->schema(function (Get $get) {
                        $type = $get('type');

                        if ($type) {
                            return ExpressionType::from($type)::form();
                        }

                        return [];
                    })
                    ->visible(fn ($get) => $get('type') !== null),

                Forms\Components\Section::make(function (Get $get) {
                    $headerParts = [__('admin.$indicator.mutator')];

                    if ($type = $get('schema.mutator.type')) {
                        $headerParts[] = '('.__("mutator.$type").')';
                    }

                    return implode(' ', $headerParts);
                })
                    ->icon('heroicon-o-calculator')
                    ->statePath('schema.mutator')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label(__('admin.type'))
                            ->options($mutatorOptions)
                            ->reactive(),

                        //- Expression type fields
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->label(__('admin.value')),

                                RawHtmlContent::make(__('mutator.expression help')),
                            ])
                            ->visible(fn (Get $get) => $get('type') === 'Expression'),

                        //- Fixed type fields
                        Forms\Components\Group::make()
                            ->schema([
                                RawHtmlContent::make(__('mutator.fixed help')),

                                Forms\Components\Repeater::make('values')
                                    ->label(__('admin.values'))
                                    ->default(1)
                                    ->columns()
                                    ->defaultItems(1)
                                    ->addActionLabel(__('admin.add'))
                                    ->schema([
                                        Forms\Components\Select::make('company')
                                            ->label(trans_choice('reference.Company', 1))
                                            ->options(Company::all()->pluck('name', 'id')->sortKeys())
                                            ->placeholder('Для всех компаний'),

                                        Forms\Components\TextInput::make('value')
                                            ->label(__('admin.value'))
                                            ->numeric(),
                                    ]),
                            ])
                            ->visible(fn (Get $get) => $get('type') === 'Fixed'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.code'))
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('module')
                    ->label(trans_choice('admin.module', 1))
                    ->wrap(),

                Tables\Columns\ToggleColumn::make('published')
                    ->label(__('admin.enabled'))
                    ->disabled(),
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
            'index' => Pages\ListIndicators::route('/'),
            'create' => Pages\CreateIndicator::route('/create'),
            'edit' => Pages\EditIndicator::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('reference.Indicator', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('reference.Indicator', 2);
    }

    public static function canEdit(Model $record): bool
    {
        return ! $record->getAttribute('module');
    }
}
