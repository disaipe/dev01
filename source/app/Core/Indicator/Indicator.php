<?php

namespace App\Core\Indicator;

use App\Core\Report\IExpression;
use App\Core\Report\IExpressionType;
use App\Models\ExpressionType;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

class Indicator
{
    /**
     * @var string unique code
     */
    public string $code;

    protected IExpressionType $type;

    /**
     * @var string display name
     */
    public string $name;

    /**
     * @var string|null module key
     */
    public ?string $module;

    public ?array $schema;

    /**
     * @var IExpression|null expression to calculate aggregated value
     */
    public ?IExpression $expression;

    /**
     * @var Closure|null resulting value mutator function, e.g. for converting bytes to gigabytes
     */
    public ?Closure $mutator;

    /**
     * Context of the indicator. Can consist report service data
     * or any additional variables to manipulate with sockets,
     * expressions, etc.
     */
    protected array $context = [];

    /**
     * Make indicator instance from array
     *
     * @param array{
     *     module?: string,
     *     code: string,
     *     type: string,
     *     name: string,
     *     schema: array,
     *     expression: IExpression,
     *     options: array,
     *     mutator?: callable(float): float,
     *   } $options options array
     *
     * @throws BindingResolutionException
     */
    public static function fromArray(array $options): Indicator
    {
        $instance = new self();
        $instance->code = Arr::get($options, 'code');
        $instance->name = Arr::get($options, 'name');
        $instance->module = Arr::get($options, 'module');
        $instance->expression = Arr::get($options, 'expression');
        $instance->mutator = Arr::get($options, 'mutator');
        $instance->schema = Arr::get($options, 'schema');

        if ($type = Arr::get($options, 'type')) {
            $expressionOptions = Arr::get($options, 'options', []);
            $expressionOptions['schema'] = $instance->schema;

            $instance->type = ExpressionType::from($type, $expressionOptions);

            if (! $instance->expression) {
                $instance->expression = $instance->type->getExpression($instance);
            }
        }

        return $instance;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function context(): array
    {
        return $this->context;
    }

    public function schema(): ?array
    {
        return $this->schema ?? [];
    }

    /**
     * Calculate indicator value
     */
    public function exec(): mixed
    {
        $result = $this->type->calculate($this);

        return $this->mutateValue($result);
    }

    public function mutateValue(float $value) {
        if (! isset($this->mutator)) {
            return $value;
        }

        $mutatorSchema = Arr::get($this->schema, 'mutator');

        return call_user_func($this->mutator, $value, $mutatorSchema, $this->context());
    }

    public function debug(): array
    {
        return $this->type->debug($this);
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
