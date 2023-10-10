<?php

namespace App\Modules\ActiveDirectory\Filament\Components;

use Closure;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;

class LdapFilterBuilder extends Builder
{
    protected array|Closure|null $fieldsOptions = null;

    protected ?LdapFilterBuilder $parent = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->createItemButtonLabel(__('admin.add'))
            ->columnSpanFull()
            ->disableItemMovement();

        if (! $this->parent) {
            $this->helperText(__('ad::messages.filters helper'));
        }
    }

    public function fields(array|Closure|null $options): static
    {
        $this->fieldsOptions = $options;

        return $this;
    }

    public function getBlocks(): array
    {
        $this->blocks([
            Builder\Block::make('where')
                ->label(__('admin.condition'))
                ->columns(1)
                ->schema([
                    TextInput::make('value')
                        ->disableLabel()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),
                ]),

            Builder\Block::make('and')
                ->label(__('admin.and'))
                ->schema(fn () => [
                    LdapFilterBuilder::make('and')
                        ->setParent($this->parent ?? $this)
                        ->fields($this->fieldsOptions)
                        ->disableLabel()
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),
                ]),

            Builder\Block::make('or')
                ->label(__('admin.or'))
                ->schema(fn () => [
                    LdapFilterBuilder::make('or')
                        ->setParent($this->parent ?? $this)
                        ->fields($this->fieldsOptions)
                        ->disableLabel()
                        ->afterStateUpdated(fn () => $this->callAfterStateUpdated()),
                ]),
        ]);

        return parent::getBlocks();
    }

    protected function setParent(?LdapFilterBuilder $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
