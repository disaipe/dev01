<?php

return [
    'name' => 'Коннектор Active Directory',
    'description' => 'Синхронизация данных из Active Directory',

    'section_sync' => 'Параметры синхронизации',

    'base dn' => 'Базовый DN',
    'base dn helper' => 'Базовый DN для поиска записей. Если не указано, будет использован базовый DN домена',

    'base dn or ou' => 'Базовый DN ',
    'base dn or ou helper' => 'Укажите одно или несколько DN на отдельных строках для ограничения'
        . ' загружаемых данных. Если не указано, будет использован базовый DN домена.'
        . ' Например:</br>'
        . '<i>OU=AllUsers,DC=domain,DC=local</i><br/>'
        . '<i>OU=Users,OU=Departament,OU=Company,DC=domain,DC=local</i>',

    'filter' => 'Фильтр',

    'ad_entry' => 'Доменная запись|Доменные записи',

    'job' => [
        'ldap sync' => [
            'title' => 'Синхронизация записей',
            'description' => 'Периодическая синхронизация записей.',
        ],
    ],
];
