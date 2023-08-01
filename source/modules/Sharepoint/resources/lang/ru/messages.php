<?php

return [
    'name' => 'Модуль интеграции Sharepoint',
    'description' => 'Синхронизация данных из Sharepoint',

    'sharepoint list' => 'Список Sharepoint|Списки Sharepoint',

    'connection settings' => 'Настройки подключения',

    'driver' => 'Драйвер подключения к базе данных',
    'host' => 'Хост',
    'port' => 'Порт',
    'login' => 'Пользователь',
    'password' => 'Пароль',
    'database' => 'База данных',

    'driver options' => 'Дополнительные опции драйвера',
    'driver options help' => 'Укажите здесь дополнительные параметры драйвера подключения в формате "ключ=значение",'
        . ' например "trust_server_certificate=1" или "encrypt=0" при работе с SQL Server. Каждый параметры должен быть'
        . ' на новой строке.',

    'list name' => 'Наименование списка в Sharepoint',
    'list site' => 'Наименование сайта в Sharepoint',

    'reference' => 'Справочник',
    'reference help' => 'Укажите справочник, в который будут сохранены загруженные данные',

    'company linking' => 'Связь с организацией',
    'company prefix field' => 'Поле с данными организации',
    'company prefix field help' => 'Укажите поле, в котором находится префикс (код) организации',

    'fields schema' => [
        'title' => 'Сопоставление данных',
        'add' => 'Добавить колонку',

        'reference' => 'Колонка справочника',
        'reference help' => 'Выберите колонку выбранного справочника',

        'list' => 'Колонка в списке',
        'list help' => 'Укажите наименование соответствующей колонки в списке',
    ],

    'action' => [
        'sync list' => [
            'title' => 'Синхронизировать данные',
            'tooltip' => 'Выгрузить данные из списка Sharepoint',

            'success' => 'Задание на синхронизацию данных из списка добавлено в очередь',
        ]
    ],

    'jobs' => [
        'sync list' => [
            'title' => 'Синхронизация списка Sharepoint ":name"',

            'errors' => [
                'sharepoint list not found' => 'Не найдена конфигурация списка Sharepoint :id',
            ],
        ],
    ]
];
