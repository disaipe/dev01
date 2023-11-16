<?php

namespace App\Core\Report;

class Expression
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
}
