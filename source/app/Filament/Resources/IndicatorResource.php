<?php

namespace App\Filament\Resources;

use App\Core\Report\ExpressionType\IndicatorSumExpressionType;
use App\Core\Report\ExpressionType\QueryExpressionType;
use App\Filament\Resources\IndicatorResource\Pages;
use App\Models\ExpressionType;
use App\Models\Indicator;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class IndicatorResource extends Resource
{
    protected static ?string $model = Indicator::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.$indicator.common'))
                    ->columns(2)
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
                            ->reactive()
                            ->columnSpanFull(),

                        Forms\Components\Checkbox::make('published')
                            ->label(__('admin.enabled')),
                    ]),

                Forms\Components\Section::make(__('admin.$indicator.schema'))
                    ->collapsible()
                    ->schema(function ($get) {
                        $type = $get('type');

                        if ($type) {
                            return ExpressionType::from($type)::form();
                        }

                        return [];
                    })
                    ->visible(fn ($get) => $get('type') !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name')),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.code')),
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

    protected static function getNavigationGroup(): ?string
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
}
