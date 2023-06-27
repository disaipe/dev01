<?php

return [
    'name' => 'Мониторинг файловых хранилищ',
    'description' => 'Синхронизация состояния файловых хранилищ',

    'file storage' => 'Файловое хранилище|Файловые хранилища',

    'base url' => 'URL службы мониторинга',
    'base url help' => 'Укажите полный URL к службе мониторинга файловых хранилищ,'
        .' например <i>http://fs-server.com:8090</i>',

    'secret' => 'Секретный ключ',
    'secret help' => 'Укажите секретный ключ, который служит для упрощенной аутентификации на службе мониторинга',

    'job' => [
        'storages sync' => [
            'title' => 'Синхронизация состояния файловых хранилищ',
            'description' => 'Периодическая синхронизация состояния файловых хранилищ - их доступность,'
                .' занимаемое дисковое пространство и т.д.',
        ],

        'storage sync' => [
            'title' => 'Синхронизация состояния файлового хранилища :storage',

            'errors' => [
                'storage not found' => 'Файловое хранилище :id не найдено',
            ],
        ],
    ],

    'action' => [
        'test service' => [
            'title' => 'Тест подключения',

            'success' => 'Подключение успешно',
            'wrong secret' => 'Неправильный secret',
            'request failed' => 'Ошибка подключения: ',
        ],
    ],
];
