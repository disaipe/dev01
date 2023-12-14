<?php

namespace App\Core\Report\Expression;

use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression;
use App\Core\Report\IQueryExpression;
use App\Forms\Components\RawHtmlContent;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SumExpression extends Expression implements IQueryExpression
{
    protected ?string $column;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->column = Arr::get($options, 'column', '*');
    }

    public function exec(Builder $query): float
    {
        return $query->sum($this->column);
    }

    public static function label(): string
    {
        return __('admin.$expression.sum');
    }

    public static function form(): array
    {
        return [
            RawHtmlContent::make(__('admin.$indicator.sum helper')),

            Select::make('column')
                ->label(__('admin.$indicator.column'))
                ->options(fn (Component $component) => static::getReferenceColumns($component))
                ->native(false)
                ->required()
        ];
    }

    public static function disabled(array $state): bool
    {
        return false;
    }

    protected static function getReferenceColumns(Component $component): array
    {
        $parentState = $component
            ->getContainer()
            ->getParentComponent() // Block
            ->getContainer()
            ->getParentComponent() // Builder
            ->getContainer()
            ->getState();

        $referenceName = Arr::get($parentState, 'schema.reference');

        if ($referenceName) {
            /** @var ReferenceManager $references */
            $references = app('references');
            $reference = $references->getByName($referenceName);

            return collect($reference->getSchema())
                ->mapWithKeys(function (ReferenceFieldSchema $field, string $key) {
                    $label = $field->getAttribute('label');

                    return [$key => $label ? "$label ($key)" : $key];
                })
                ->sort()
                ->toArray();
        }

        return [];
    }
}
