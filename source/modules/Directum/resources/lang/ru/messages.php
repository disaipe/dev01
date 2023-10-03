<?php

return [
    'name' => 'Модуль Directum',
    'description' => 'Синхронизация данных из СЭД Directum',

    'connection settings' => 'Настройки подключения',

    'job' => [
        'users' => [
            'title' => 'Синхронизация пользователей Directum',
            'description' => 'Периодическая синхронизация количества зарегистрированных пользователей в системе Directum.',
        ],
    ],
];
