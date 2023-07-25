<?php

namespace App\Filament\Resources;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\Expression;
use App\Core\Report\Expression\ExpressionManager;
use App\Filament\Components\ConditionBuilder;
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
        $referencesOptions = static::getReferencesOptions();

        /** @var ExpressionManager $expressions */
        $expressionsManager = app('expressions');
        $expressions = $expressionsManager->getExpressions();

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

                        Forms\Components\Select::make('schema.reference')
                            ->label(trans_choice('admin.reference', 1))
                            ->options($referencesOptions)
                            ->columnSpanFull()
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(static::updateReferenceFields(...))
                            ->afterStateUpdated(static::updateReferenceFields(...)),

                        Forms\Components\Checkbox::make('published')
                            ->label(__('admin.enabled')),
                    ]),

                Forms\Components\Section::make(__('admin.$indicator.schema'))
                    ->collapsible()
                    ->schema([
                        Builder::make('schema.values')
                            ->label('')
                            ->required()
                            ->createItemButtonLabel(__('admin.$indicator.schema add'))
                            ->blocks(Arr::map($expressions, function (Expression|string $expression) {
                                return Builder\Block::make(class_basename($expression))
                                    ->label($expression::label())
                                    ->schema($expression::form());
                            }))
                            ->minItems(1)
                            ->maxItems(1),
                    ]),

                Forms\Components\Section::make(__('admin.$indicator.conditions'))
                    ->collapsible()
                    ->schema([
                        RawHtmlContent::make(__('admin.$indicator.conditions helper')),

                        ConditionBuilder::make('schema.conditions')
                            ->disableLabel()
                            ->reactive()
                            ->fields(fn ($get) => $get('__referenceFields')),

                        Forms\Components\Section::make(__('admin.$indicator.placeholders'))
                            ->collapsed()
                            ->schema([
                                Forms\Components\ViewField::make('placeholdersHelp')
                                    ->view('admin.help.indicatorPlaceholders')
                            ])
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

    protected static function getReferencesOptions(): array
    {
        $references = app('references')->getReferences();

        $referencesOptions = [];

        foreach ($references as $reference) {
            /** @var ReferenceEntry $reference */
            if ($reference->canAttachIndicators()) {
                $referencesOptions[$reference->getName()] = $reference->getLabel();
            }
        }

        return Arr::sort($referencesOptions);
    }

    protected static function updateReferenceFields($state, $set): void
    {
        if (! $state) {
            $set('__referenceFields', []);

            return;
        }

        /** @var ReferenceManager $references */
        $references = app('references');
        $reference = $references->getByName($state);

        $schema = $reference->getSchema();
        $model = $reference->getModelInstance();
        $filteredFields = Arr::where($schema, function (ReferenceFieldSchema $field, string $key) use ($model) {
            return ! $model->isRelation($key);
        });

        $fields = Arr::map($filteredFields, function (ReferenceFieldSchema $field, $key) {
            $label = $field->getAttribute('label');

            return $label ? "$key ({$label})" : $key;
        }, []);

        $set('__referenceFields', $fields);
    }
}
