<?php

return [
    'name' => 'Коннектор Active Directory',
    'description' => 'Синхронизация данных из Active Directory',

    'section_sync' => 'Параметры синхронизации',

    'base dn' => 'Базовый DN',
    'base dn helper' => 'Базовый DN для поиска записей. Если не указано, будет использован базовый DN домена',

    'filter' => 'Фильтр',

    'ad_entry' => 'Доменная запись|Доменные записи',

    'job' => [
        'ldap sync' => [
            'title' => 'Синхронизация записей',
            'description' => 'Периодическая синхронизация записей.',
        ],
    ],
];
