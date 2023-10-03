<?php

return [
    'sql connection settings' => [
        'driver' => 'Драйвер подключения к базе данных',
        'host' => 'Хост',
        'port' => 'Порт',
        'login' => 'Пользователь',
        'password' => 'Пароль',
        'database' => 'База данных',

        'driver options' => 'Дополнительные опции драйвера',
        'driver options help' => 'Укажите здесь дополнительные параметры драйвера подключения в формате "ключ=значение",'
            .' например "trust_server_certificate=1" или "encrypt=0" при работе с SQL Server. Каждый параметры должен быть'
            .' на новой строке.',

        'test connection' => 'Тест соединения',
    ],
];
