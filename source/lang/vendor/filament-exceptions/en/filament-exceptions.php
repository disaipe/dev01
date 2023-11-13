<?php

return [

    'labels' => [
        'model' => 'Исключение',
        'model_plural' => 'Исключения',
        'navigation' => 'Исключения',
        'navigation_group' => 'Отладка',

        'pills' => [
            'exception' => 'Exception',
            'headers' => 'Headers',
            'cookies' => 'Cookies',
            'body' => 'Body',
            'queries' => 'Queries',
        ],
    ],

    'empty_list' => 'Ура! Ошибок нет или их кто-то удалил 😎',

    'columns' => [
        'method' => 'Метод',
        'path' => 'URL',
        'type' => 'Тип',
        'code' => 'Код',
        'ip' => 'IP',
        'occurred_at' => 'Дата',
    ],

];
