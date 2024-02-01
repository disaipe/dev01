<?php

namespace App\Core\Traits;

trait Filterable
{
    use \Abbasudo\Purity\Traits\Filterable;

    /**
     * Must be declared to avoid problems with the array getting into the model attributes
     *
     * @var array
     */
    public $filterFields = [];
}
