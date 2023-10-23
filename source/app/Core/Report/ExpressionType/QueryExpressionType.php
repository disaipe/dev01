<?php

namespace App\Core\Report\ExpressionType;

use App\Core\Enums\ReportContextConstant;
use App\Core\Indicator\Indicator;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\ExpressionManager;
use App\Core\Report\IExpression;
use App\Core\Report\IExpressionType;
use App\Core\Utils\QueryConditionsBuilder;
use App\Filament\Components\ConditionBuilder;
use App\Forms\Components\RawHtmlContent;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class QueryExpressionType implements IExpressionType
{
    /**
     * @var string|null model class name
     */
    public ?string $model;

    /**
     * @var mixed data query
     */
    public mixed $query;

    /**
     * @var array|null query conditions
     */
    public ?array $conditions;

    /**
     * Scopes what will be applied to the indicator query
     *
     * Can consist only name of the model scope (e.g. `'company' => true`),
     * or callable (e.g. `'company' => fn (Builder $q) => $q->...`)
     *
     * Callable params: Builder $query, [array $context]
     *
     * @var array|Closure[]
     */
    protected array $scopes = [];

    protected ?array $context = [];

    public function __construct(array $options = [])
    {
        $this->scopes = [
            'company' => function (Builder $query) {
                if (! $query->hasNamedScope('company')) {
                    return;
                }

                $query->company(Arr::get($this->context, ReportContextConstant::COMPANY_CODE->name));
            },
            'period' => function (Builder $query) {
                if (! $query->hasNamedScope('period')) {
                    return;
                }

                $period = Arr::get($this->context, ReportContextConstant::PERIOD->name);

                if (get_class($period) === Carbon::class && $period->isValid()) {
                    $from = $period->copy()->startOfMonth();
                    $to = $period->copy()->endOfMonth();

                    $query->period($from, $to);
                }
            },
        ];

        $this->setOptions($options);
    }

    public static function form(): array
    {
        $referencesOptions = static::getReferencesOptions();

        /** @var ExpressionManager $expressions */
        $expressionsManager = app('expressions');
        $expressions = $expressionsManager->getExpressions();

        return [
            Components\Select::make('schema.reference')
                ->label(trans_choice('admin.reference', 1))
                ->options($referencesOptions)
                ->columnSpanFull()
                ->required()
                ->reactive()
                ->afterStateHydrated(static::updateReferenceFields(...))
                ->afterStateUpdated(static::updateReferenceFields(...)),

            Components\Builder::make('schema.values')
                ->label('')
                ->required()
                ->addActionLabel(__('admin.$indicator.schema add'))
                ->blocks(Arr::map($expressions, function (IExpression|string $expression) {
                    return Components\Builder\Block::make(class_basename($expression))
                        ->label($expression::label())
                        ->schema($expression::form());
                }))
                ->minItems(1)
                ->maxItems(1),

            Components\Section::make(__('admin.$indicator.conditions'))
                ->icon('heroicon-o-funnel')
                ->collapsed()
                ->schema([
                    RawHtmlContent::make(__('admin.$indicator.conditions helper')),

                    ConditionBuilder::make('schema.conditions')
                        ->hiddenLabel()
                        ->reactive()
                        ->fields(fn ($get) => $get('__referenceFields')),

                    Components\Section::make(__('admin.$indicator.placeholders'))
                        ->collapsed()
                        ->schema([
                            Components\ViewField::make('placeholdersHelp')
                                ->view('admin.help.indicatorPlaceholders'),
                        ]),
                ]),
        ];
    }

    public static function label(): string
    {
        return __('admin.$indicator.type.query expression');
    }

    protected function setOptions(array $options): void
    {
        $this->model = Arr::get($options, 'model');
        if (! $this->model) {
            $this->model = Arr::get($options, 'schema.reference');
        }

        $this->query = Arr::get($options, 'query');
        $this->conditions = Arr::get($options, 'conditions');

        $this->addScopes(Arr::get($options, 'scopes') ?? []);
    }

    public function addScopes(array $scopes): self
    {
        $this->scopes = collect($this->scopes ?? [])
            ->merge($scopes)
            ->toArray();

        return $this;
    }

    public function getExpression(Indicator $indicator): ?IExpression
    {
        [$expression] = Arr::get($indicator->schema(), 'values') ?? [null];

        if ($expression) {
            $type = Arr::get($expression, 'type');
            $data = Arr::get($expression, 'data') ?? [];

            /** @var ExpressionManager $expressions */
            $expressions = app('expressions');
            $expression = $expressions->getByKey($type);

            if (class_exists($expression)) {
                return new $expression(...$data);
            }
        }

        return null;
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

        if (! $reference) {
            Notification::make()->danger()->title(__('error.reference not found', ['reference' => $state]))->send();

            return;
        }

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

    /**
     * @throws Exception
     */
    public function calculate(Indicator $indicator)
    {
        $query = $this->getPreparedQuery($indicator);

        return $indicator->expression?->exec($query);
    }

    public function debug(Indicator $indicator): array
    {
        $query = $this->getPreparedQuery($indicator);
        $data = $query->get();

        /** @var ReferenceManager $references */
        $references = app('references');
        $reference = $references->getByName(class_basename($this->model));

        return [
            'reference' => $reference?->getName(),
            'data' => $data,
        ];
    }

    protected function getPreparedQuery(Indicator $indicator): Builder
    {
        $this->context = $indicator->context();

        $query = $this->getModelQuery($this->model);
        $this->applyScopes($query);
        $this->applyConditions($query);

        // Apply indicator query modification
        $execQuery = $this->query
            ? ($this->query)($query)
            : $query;

        return $execQuery;
    }

    /**
     * Get model or reference query
     *
     * @throws Exception
     */
    protected function getModelQuery(string $model): Builder
    {
        if (is_subclass_of($model, Model::class)) {
            return $model::query();
        }

        /** @var ReferenceManager $referenceManager */
        $referenceManager = app('references');

        if ($reference = $referenceManager->getByName($model)) {
            $model = $reference->getModelInstance();

            return $model->query();
        }

        throw new Exception("Модель данных '{$model}' не определена");
    }

    /**
     * Apply scopes to query
     */
    protected function applyScopes(Builder $query): Builder
    {
        foreach ($this->scopes as $name => $scope) {
            if ($scope) {
                if (is_string($name) && is_bool($scope) && $query->hasNamedScope($name)) {
                    $query->$name();
                } elseif (is_callable($scope)) {
                    call_user_func($scope, $query, $this->context);
                }
            }
        }

        return $query;
    }

    protected function applyConditions(Builder $query): Builder
    {
        if ($this->conditions) {
            QueryConditionsBuilder::applyToQuery($query, $this->conditions, $this->context);
        }

        return $query;
    }
}
