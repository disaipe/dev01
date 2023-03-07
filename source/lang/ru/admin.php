<?php

return [
    'user' => 'Пользователь|Пользователи',
    'domain' => 'Домен|Домены',
    'module' => 'Модуль|Модули',
    'reference' => 'Справочник|Справочники',

    'code' => 'Код',
    'name' => 'Наименование',
    'login' => 'Логин',
    'password' => 'Пароль',
    'email' => 'E-mail',
    'identity' => 'Идентификатор',
    'host' => 'Хост',
    'port' => 'Порт',
    'timeout' => 'Таймаут',
    'schedule' => 'Расписание',

    'enabled' => 'Активно',
    'system' => 'Системное',

    'cron_helper' => '<pre class="text-xs ml-1">
 - - - - -
 | | | | |
 | | | | |
 | | | | +----- day of week (0 - 7) (Sunday=0 or 7)
 | | | +---------- month (1 - 12)
 | | +--------------- day of month (1 - 31)
 | +-------------------- hour (0 - 23)
 +------------------------- min (0 - 59)</pre>',

    'menu' => [
        'access' => 'Доступ',
    ],

    '$user' => [
        'name' => 'Имя пользователя',
    ],

    '$domain' => [
        'username' => 'Служебный пользователь',
        'base_dn' => 'Базовый DN',
        'base_dn_helper' => 'Например, "dc=domain,dc=local"',
    ],

    '$indicator' => [
        'common' => 'Базовая конфигурация',
        'schema' => 'Схема',
        'column' => 'Колонка',
    ],

    '$expression' => [
        'count' => 'Количество',
        'sum' => 'Сумма',
    ],
];
