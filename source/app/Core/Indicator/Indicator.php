<?php

namespace App\Core\Indicator;

use App\Core\Report\Expression\Expression;
use App\Core\Report\Expression\ExpressionManager;
use App\Core\Utils\QueryConditionsBuilder;
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
     * Make indicator instance from array
     *
     * @param  array  $options options array
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

    /**
     * Calculate indicator value
     *
     * @param  Builder  $query query modifier
     * @param  array  $context query context
     */
    public function exec(Builder $query, array $context = []): mixed
    {
        $expressionQuery = $this->query
            ? ($this->query)($query)
            : $query;

        if ($this->conditions) {
            QueryConditionsBuilder::applyToQuery($expressionQuery, $this->conditions, $context);
        }

        $result = $this->expression?->exec($expressionQuery);

        if (isset($this->mutator)) {
            $result = call_user_func($this->mutator, $result);
        }

        return $result;
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
