<?php

namespace App\Core\Report;

use Illuminate\Support\Arr;

class Expression
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Get expression options or option by key using "dot" notation
     */
    public function getOptions(?string $key = null): mixed
    {
        if ($key) {
            return Arr::get($this->options, $key);
        }

        return $this->options;
    }
}
