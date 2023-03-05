<?php

namespace App\Core\Indicator;

use App\Core\Report\Expression\Expression;
use Illuminate\Support\Arr;

class Indicator
{
    public string $code;
    public string $name;
    public string $model;
    public string $module;
    public $query;
    public Expression $expression;

    public static function fromArray($options): Indicator
    {
        $instance = new self();
        $instance->code = Arr::get($options, 'code');
        $instance->name = Arr::get($options, 'name');
        $instance->model = Arr::get($options, 'model');
        $instance->module = Arr::get($options, 'module');
        $instance->query = Arr::get($options, 'query');
        $instance->expression = Arr::get($options, 'expression');

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'module' => $this->module
        ];
    }
}
