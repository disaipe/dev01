<?php

namespace App;

class Directives
{
    public static function vue(): string
    {
        return <<<'blade'
            <div id='app' data-page='{{ $vueData }}'></div>
        blade;
    }
}
