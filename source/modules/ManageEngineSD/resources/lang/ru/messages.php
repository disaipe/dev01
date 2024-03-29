<?php

return [
    'name' => 'Интеграция с ManageEngine SD',
    'description' => 'Сбор статистики по закрытым заявкам',

    'workorder' => 'Заявка ManageEngine SD|Заявки ManageEngine SD',

    'host' => 'Хост',
    'port' => 'Порт',
    'login' => 'Пользователь',
    'password' => 'Пароль',
    'database' => 'База данных',

    'settings' => 'Параметры',

    'connection settings' => 'Настройка подключения',

    'closed statuses' => 'Статус закрытой заявки',
    'closed statuses help' => 'Если статус не указан, то в выборку будут попадать все заявки'
        .' без какой-либо дополнительной фильтрации',

    '$expression' => [
        '$timer' => [
            'label' => '[MESD] Сумма часов по заявкам',
            'service' => 'Услуга',
            'service help' => 'В качестве показателя будет возвращена сумма затраченного времени'
                .' по выбранным категориям услуг в часах.',
        ],

        '$counter' => [
            'label' => '[MESD] Количество заявок',
            'service' => 'Услуга',
            'service help' => 'В качестве показателя будет возвращено количество выполненных заявок'
                .' по выбранным категориям услуг.',
        ],
    ],
];
