<?php

namespace App\Filament\Resources;

use App\Core\Reference\ReferenceEntry;
use App\Filament\Resources\IndicatorResource\Pages;
use App\Models\Indicator;
use Filament\Forms;
use Filament\Forms\Components\Builder;
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
        $references = app('references')->getReferences();

        $options = [];

        foreach ($references as $reference) {
            /** @var ReferenceEntry $reference */
            $options[$reference->getModel()] = $reference->getLabel();
        }

        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.$indicator.common'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.name'))
                            ->maxLength(256)
                            ->required(),

                        Forms\Components\Select::make('schema.reference')
                            ->label(trans_choice('admin.reference', 1))
                            ->options($options)
                            ->required(),

                        Forms\Components\Checkbox::make('published')
                            ->label(__('admin.enabled')),
                    ]),

                Forms\Components\Section::make(__('admin.$indicator.schema'))
                    ->schema([
                        Builder::make('schema.values')
                            ->label('')
                            ->blocks([
                                Builder\Block::make('CountExpression')
                                    ->label(__('admin.$expression.count'))
                                    ->schema([]),
                                Builder\Block::make('SumExpression')
                                    ->label(__('admin.$expression.sum'))
                                    ->schema([
                                        Forms\Components\TextInput::make('column')
                                            ->label(__('admin.$indicator.column'))
                                            ->required(),
                                    ]),
                            ])
                            ->minItems(1)
                            ->maxItems(1),
                    ]),
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
            'index' => Pages\ListIndicators::route('/'),
            'create' => Pages\CreateIndicator::route('/create'),
            'edit' => Pages\EditIndicator::route('/{record}/edit'),
        ];
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
