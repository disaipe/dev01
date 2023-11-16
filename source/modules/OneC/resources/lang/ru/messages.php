<?php

return [
    'name' => 'Модуль 1С',
    'description' => 'Синхронизация данных из учетной системы 1С',

    'info base' => 'Информационная база данных|Информационные базы данных',
    'info base user' => 'Пользователь 1С|Пользователи 1С',

    'rpc settings' => 'Настройки подключения к RPC API',

    'files path' => 'Путь к файлам списков баз данных',
    'files path help' => 'Укажите один или несколько путей к файлам списков баз данных'
        . ' <i>v8i</i>. Каждый путь должен быть на отдельной строке.',

    'job' => [
        'list sync' => [
            'title' => 'Парсинг списков общих информационных баз',
            'description' => 'Периодический парсинг списка информационных баз из файлов списков.'
                . ' Производится сбор данных по существующим ИБ и серверам 1С, на которых они располагаются.',
        ],

        'server list sync' => [
            'title' => 'Синхронизация серверных списков',
            'description' => 'Периодическая синхронизация данных из серверных список информационных баз.'
                . ' Производится сбор данных для подключения к БД существующих ИБ.',
        ],

        'server users sync' => [
            'title' => 'Синхронизация пользователей',
            'description' => 'Периодическая синхронизация списка пользователей ИБ.',
        ],
    ],
];