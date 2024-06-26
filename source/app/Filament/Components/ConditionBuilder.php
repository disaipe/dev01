<?php

namespace App\Filament\Components;

use App\Core\Enums\QueryConditionOperator;
use Closure;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class ConditionBuilder extends Builder
{
    protected array|Closure|null $fieldsOptions = null;

    protected ?ConditionBuilder $parent = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->addActionLabel(__('admin.add'))
            ->columnSpanFull()
            ->reorderable();
    }

    public function fields(array|Closure|null $options): static
    {
        $this->fieldsOptions = $options;

        return $this;
    }

    public function getBlocks(): array
    {
        $operatorsWithoutValue = [
            QueryConditionOperator::IS_NULL->value,
            QueryConditionOperator::IS_NOT_NULL->value,
        ];

        $this->blocks([
            Builder\Block::make('where')
                ->label(__('admin.where'))
                ->columns(3)
                ->schema([
                    $this->getFieldInput(),

                    Select::make('condition')
                        ->label(__('admin.condition'))
                        ->options(QueryConditionOperator::toValuesArray())
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),

                    TextInput::make('value')
                        ->label(__('admin.value'))
                        ->required()
                        ->reactive()
                        ->disabled(fn (Get $get) => in_array($get('condition'), $operatorsWithoutValue))
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),
                ]),

            Builder\Block::make('and')
                ->label(__('admin.and'))
                ->schema(fn () => [
                    ConditionBuilder::make('and')
                        ->setParent($this->parent ?? $this)
                        ->fields($this->fieldsOptions)
                        ->hiddenLabel()
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),
                ]),

            Builder\Block::make('or')
                ->label(__('admin.or'))
                ->schema(fn () => [
                    ConditionBuilder::make('or')
                        ->setParent($this->parent ?? $this)
                        ->fields($this->fieldsOptions)
                        ->hiddenLabel()
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),
                ]),

            Builder\Block::make('raw')
                ->label(__('admin.$indicator.raw'))
                ->schema(fn () => [
                    TextInput::make('value')
                        ->helperText(__('admin.$indicator.raw condition help')),
                ]),
        ]);

        return parent::getBlocks();
    }

    protected function setParent(?ConditionBuilder $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    protected function getFieldInput(): Field
    {
        if ($this->fieldsOptions) {
            $component = Select::make('field')
                ->options(($this->parent ?? $this)->evaluate($this->fieldsOptions))
                ->reactive();
        } else {
            $component = TextInput::make('field');
        }

        return $component
            ->label(trans_choice('admin.field', 1))
            ->required()
            ->reactive()
            ->afterStateUpdated(fn () => $this->callAfterStateUpdated());
    }
}
