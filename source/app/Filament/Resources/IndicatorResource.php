<?php

namespace App\Filament\Resources;

use App\Core\Reference\ReferenceEntry;
use App\Filament\Resources\IndicatorResource\Pages;
use App\Forms\Components\RawHtmlContent;
use App\Models\Indicator;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Arr;

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
            if ($reference->canAttachIndicators()) {
                $options[$reference->getName()] = $reference->getLabel();
            }
        }

        $options = Arr::sort($options);

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
                            ->regex('[a-zA-Z_-]+')
                            ->maxLength(32)
                            ->disabled(fn ($record) => isset($record)),

                        Forms\Components\Select::make('schema.reference')
                            ->label(trans_choice('admin.reference', 1))
                            ->options($options)
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\Checkbox::make('published')
                            ->label(__('admin.enabled')),
                    ]),

                Forms\Components\Section::make(__('admin.$indicator.schema'))
                    ->schema([
                        Builder::make('schema.values')
                            ->label('')
                            ->required()
                            ->createItemButtonLabel(__('admin.$indicator.schema add'))
                            ->blocks([
                                Builder\Block::make('CountExpression')
                                    ->label(__('admin.$expression.count'))
                                    ->schema([
                                        RawHtmlContent::make(__('admin.$indicator.count helper')),
                                    ]),
                                Builder\Block::make('SumExpression')
                                    ->label(__('admin.$expression.sum'))
                                    ->schema([
                                        RawHtmlContent::make(__('admin.$indicator.sum helper')),
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
