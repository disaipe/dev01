<?php

namespace App\Core\Indicator;

use App\Core\Enums\ReportContextConstant;
use App\Core\Report\Expression\Expression;
use App\Core\Report\Expression\ExpressionManager;
use App\Core\Utils\QueryConditionsBuilder;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class Indicator
{
    /**
     * @var string unique code
     */
    public string $code;

    /**
     * @var string display name
     */
    public string $name;

    /**
     * @var string model class name
     */
    public string $model;

    /**
     * @var string|null module key
     */
    public ?string $module;

    /** @var array|null query conditions */
    public ?array $conditions;

    /**
     * @var mixed data query
     */
    public mixed $query;

    /**
     * @var Expression|null expression to calculate aggregated value
     */
    public ?Expression $expression;

    /**
     * @var Closure|null resulting value mutator function, e.g. for converting bytes to gigabytes
     */
    public ?Closure $mutator;

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
    protected array $scopes;

    /**
     * Context of the indicator. Can consist report service data
     * or any additional variables to manipulate with sockets,
     * expressions, etc.
     *
     * @var array
     */
    protected array $context = [];

    public function __construct()
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
            }
        ];
    }

    /**
     * Make indicator instance from array
     *
     * @param  array{
     *     module?: string,
     *     code: string,
     *     name: string,
     *     model: string,
     *     query?: callable(Builder): Builder,
     *     expression: Expression,
     *     mutator?: callable(float): float,
     *     conditions?: array,
     *     scopes?: iterable<string,bool>|iterable<string, callable(Builder): void>,
     *   } $options options array
     */
    public static function fromArray(array $options): Indicator
    {
        $instance = new self();
        $instance->code = Arr::get($options, 'code');
        $instance->name = Arr::get($options, 'name');
        $instance->model = Arr::get($options, 'model');
        $instance->module = Arr::get($options, 'module');
        $instance->query = Arr::get($options, 'query');
        $instance->expression = Arr::get($options, 'expression');
        $instance->mutator = Arr::get($options, 'mutator');
        $instance->conditions = Arr::get($options, 'conditions');

        $instance->addScopes(Arr::get($options, 'scopes') ?? []);

        return $instance;
    }

    /**
     * Make indicator instance from model
     */
    public static function fromModel(\App\Models\Indicator $model): Indicator
    {
        $instance = new self();
        $instance->code = $model->code;
        $instance->name = $model->name;
        $instance->model = Arr::get($model->schema, 'reference');
        $instance->module = Arr::get($model->schema, 'module');
        $instance->conditions = Arr::get($model->schema, 'conditions');
        $instance->query = null;

        [$expression] = Arr::get($model->schema, 'values', []);
        if ($expression) {
            $type = Arr::get($expression, 'type');
            $data = Arr::get($expression, 'data') ?? [];

            /** @var ExpressionManager $expressions */
            $expressions = app('expressions');
            $expression = $expressions->getByKey($type);

            if (class_exists($expression)) {
                $instance->expression = new $expression(...$data);
            } else {
                $instance->expression = null;
            }
        }

        return $instance;
    }

    public function addContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function addScopes(array $scopes): self
    {
        $this->scopes = collect($this->scopes)
            ->merge($scopes)
            ->toArray();

        return $this;
    }

    public function applyScopes(Builder $query): self
    {
        foreach ($this->scopes as $name => $scope) {
            if ($scope) {
                if (is_string($name) && is_bool($scope) && $query->hasNamedScope($name)) {
                    $query->$name();
                } else if (is_callable($scope)) {
                    call_user_func($scope, $query, $this->context);
                }
            }
        }

        return $this;
    }

    public function scopes(): array
    {
        return $this->scopes;
    }

    /**
     * Calculate indicator value
     *
     * @param  Builder  $query query modifier
     */
    public function exec(Builder $query): mixed
    {
        $result = $this->expression?->exec($this->makeQuery($query));

        if (isset($this->mutator)) {
            $result = call_user_func($this->mutator, $result);
        }

        return $result;
    }

    public function makeQuery(Builder $query): Builder
    {
        $expressionQuery = $this->query
            ? ($this->query)($query)
            : $query;

        if ($this->conditions) {
            QueryConditionsBuilder::applyToQuery($expressionQuery, $this->conditions, $this->context);
        }

        return $expressionQuery;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'module' => $this->module,
        ];
    }
}
